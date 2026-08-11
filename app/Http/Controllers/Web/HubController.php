<?php

namespace App\Http\Controllers\Web;

use App\Events\AgentStarted;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManageKeyRotationRequest;
use App\Http\Requests\SaveAgentSettingsRequest;
use App\Http\Requests\SendPeopleConnectMessageRequest;
use App\Jobs\PeopleConnect\SyncWahaContactsJob as RealSyncWahaContactsJob;
use App\Jobs\PeopleConnect\SyncWahaMessagesJob as RealSyncWahaMessagesJob;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\AIApiKey;
use App\Models\AIModel;
use App\Models\AIProvider;
use App\Models\Contact;
use App\Models\ContactMessage;
use App\Models\HedrasoulApprovalRequest;
use App\Models\HedrasoulMessage;
use App\Models\HedrasoulNotification;
use App\Models\HedrasoulSession;
use App\Models\HermesMessage;
use App\Models\HermesSession;
use App\Models\IntentRouting;
use App\Models\Memory;
use App\Models\NotificationLog;
use App\Models\PeopleConnect\PeopleConnectConversation;
use App\Models\PeopleConnect\PeopleConnectMessage;
use App\Models\ProactiveTrigger;
use App\Models\Setting;
use App\Models\WahaSyncProcess;
use App\Models\WorkflowExecution;
use App\Models\WorkflowSchedule;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use App\Services\LogService;
use App\Services\Mock\ContactStudioEgMock;
use App\Services\Mock\GrandArchivesMock;
use App\Services\Mock\WarRoomMock;
use App\Services\NexusDashboardService;
use App\Services\PeopleConnect\SendContactMessageAction;
use App\Services\SettingCacheService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HubController extends Controller
{
    public function dashboard()
    {
        $totalContacts = Contact::count();
        $contactDelta = Contact::where('created_at', '>=', now()->startOfDay())->count();

        $activeExecutes = WorkflowExecution::whereIn('status', ['running', 'pending'])->count();
        $activeTasksCount = 0;
        try {
            $activeTasksCount = AgentTask::whereIn('status', ['running', 'in_progress', 'queued', 'pending'])->count();
        } catch (\Exception $e) {
        }

        $agentCount = Agent::count();
        $onlineAgentsCount = Agent::where('status', 'active')->count();
        $totalAgentsCount = $agentCount;

        $activeAgent = Agent::where('status', 'active')->first() ?: Agent::first();
        $activeAgentModel = $activeAgent ? strtoupper($activeAgent->model) : 'GEMINI';

        $memoryCount = 0;
        $memoryDelta = 0;
        try {
            $memoryCount = \DB::table('memories')->count();
            $memoryDelta = \DB::table('memories')->where('created_at', '>=', now()->startOfDay())->count();
        } catch (\Exception $e) {
        }

        // Recent contacts for dashboard panel
        $recentContacts = Contact::orderBy('updated_at', 'desc')->take(6)->get();

        // Agents for status panel
        $agents = Agent::orderBy('status', 'asc')->take(6)->get();

        // Upcoming schedules
        $upcomingSchedules = [];
        try {
            $upcomingSchedules = WorkflowSchedule::where('is_active', true)
                ->orderBy('next_run_at', 'asc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
        }

        // Recent activity logs for telemetry (using logs table)
        $recentLogs = [];
        try {
            $recentLogs = \DB::table('logs')
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get()
                ->reverse()
                ->values();
        } catch (\Exception $e) {
        }

        return view('hubs.dashboard', compact(
            'totalContacts', 'contactDelta', 'activeExecutes', 'activeTasksCount',
            'agentCount', 'onlineAgentsCount', 'totalAgentsCount', 'activeAgentModel',
            'memoryCount', 'memoryDelta', 'recentContacts', 'agents',
            'upcomingSchedules', 'recentLogs'
        ));
    }

    public function contacts(Request $request)
    {
        $totalContacts = Contact::count();
        $wahaContacts = Contact::whereNotNull('waha_contact_id')->count();
        $autopilotCount = Contact::where('reply_mode_override', 'autopilot')->count();
        $copilotCount = Contact::where('reply_mode_override', 'copilot')->count();

        $query = Contact::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('mode') && $request->mode !== 'all') {
            if ($request->mode === 'manual') {
                $query->where(function ($q) {
                    $q->where('reply_mode_override', 'manual')->orWhereNull('reply_mode_override');
                });
            } else {
                $query->where('reply_mode_override', $request->mode);
            }
        }

        if ($request->filled('waha') && $request->waha == '1') {
            $query->whereNotNull('waha_contact_id');
        }

        if ($request->filled('favorites') && $request->favorites == '1') {
            $user = $request->user();
            if ($user) {
                $query->whereHas('favoritedBy', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(24)->withQueryString();

        return view('hubs.contacts', compact('contacts', 'totalContacts', 'wahaContacts', 'autopilotCount', 'copilotCount'));
    }

    public function contactProfile($id)
    {
        $contact = Contact::findOrFail($id);

        $auditEvents = \DB::table('contact_audit_events')
            ->where('contact_id', $contact->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $stats = [
            'total_messages' => ContactMessage::where('contact_id', $contact->id)->count(),
            'inbound' => ContactMessage::where('contact_id', $contact->id)->where('direction', 'inbound')->count(),
            'outbound' => ContactMessage::where('contact_id', $contact->id)->where('direction', 'outbound')->count(),
            'has_media' => ContactMessage::where('contact_id', $contact->id)->whereNotNull('attachments_metadata')->count(),
        ];

        $messages = ContactMessage::where('contact_id', $contact->id)
            ->orderBy('source_timestamp', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(100, ['*'], 'msg_page');

        return view('hubs.contact-profile', compact('contact', 'auditEvents', 'stats', 'messages'));
    }

    public function contactStudio(int $id)
    {
        $contact = Contact::findOrFail($id);

        // ─────────────────────────────────────────────────────────────
        //  CONTACT STUDIO — Rich Mock Profile Object (120 fields)
        //  Sample persona: Ahmed Zeidan / Contact: Marline
        //  Used for UI building until DB persistence layer is added.
        // ─────────────────────────────────────────────────────────────
        $profile = [

            // ── 1. CORE IDENTITY ──────────────────────────────────────
            'identity' => [
                'full_name' => $contact->name ?? 'Ahmed Zeidan',
                'nicknames_used_by_hedra' => ['يا مبي', 'أيوه إنت', 'يا ابني'],
                'nicknames_used_by_contact' => ['هدرا', 'هدوهد', 'الزعيم'],
                'phone_numbers' => array_filter([$contact->phone]),
                'gender' => 'Male',
                'estimated_age' => 28,
                'relationship_type' => 'حبيبة سابقة',
                'relationship_status' => 'في خلاف — محاولة رجوع',
            ],

            // ── 2. PERSONAL PROFILE ───────────────────────────────────
            'personal' => [
                'city' => 'القاهرة — مدينة نصر',
                'workplace' => $contact->company ?? 'شركة تقنية',
                'job_title' => $contact->title ?? 'مطور برمجيات',
                'boss_name' => 'Engineer Tariq',
                'marital_status' => 'أعزب',
                'has_children' => false,
                'family_members_mentioned' => ['والده — 65 سنة', 'والدته — متوترة', 'أخته الصغيرة'],
                'education_level' => 'بكالوريوس هندسة حاسبات',
                'appearance_notes' => 'طويل، بشرة قمحية، شعر قصير، مظهر كاجوال دايماً',
                'personality_traits_positive' => ['صريح', 'وفي مع الناس اللي بيحبها', 'ذكي جداً', 'كريم'],
                'personality_traits_negative' => ['مزاجي', 'سريع الغضب', 'انتقامي في اللحظات الصعبة'],
                'secrets_confided' => 'حكتله عن علاقة سابقة فشلت — وعن أزمة مع أهلها',
            ],

            // ── 3. COMMUNICATION PATTERNS ────────────────────────────
            'communication' => [
                'linguistic_register' => 'عامية مصرية جداً مع بعض إنجليزي تقني',
                'catchphrases_hedra' => ['نضفي ضميرك', 'ياخوانا', 'احااا', 'يا مبي'],
                'catchphrases_contact' => ['الووووووو', 'يا عيني', 'هههههه', 'حقك فوق راسي'],
                'emoji_usage_hedra' => ['🔥', '😤', '💀', '🤷‍♂️'],
                'emoji_usage_contact' => ['☺️', '😊', '🙏', '💕'],
                'voice_note_frequency' => 'متوسط — بيستخدموا فويس في اللحظات العاطفية',
                'media_sharing_style' => 'صور شخصية، لينكات أغاني، ميمز',
                'call_frequency' => 'قليل — معظمه رسايل',
                'response_time_pattern' => 'هي بتتأخر أحياناً عمداً — هو بيرد بسرعة وقت الصلح',
                'active_hours' => 'مسا من 9 م لـ 2 ص',
                'block_history' => 'بلوك وفك بلوك 3 مرات على الأقل',
                'message_deletion_pattern' => 'هو بيمسح رسايل غضب بعد ما يبرد',
                'conversation_initiator' => 'مارلين في الغالب بعد فترة الصمت',
                'avg_message_length' => 'طويلة عنده — قصيرة ومبهمة عندها أحياناً',
                'topic_initiation_style' => 'هو مباشر — هي تدريجية وبتفتح بموضوع جانبي',
            ],

            // ── 4. BIG FIVE (for SVG radar — 0 to 100) ──────────────
            'big_five' => [
                'openness' => 82,
                'conscientiousness' => 48,
                'extraversion' => 55,
                'agreeableness' => 28,
                'neuroticism' => 74,
            ],

            // ── 5. PSYCHOLOGICAL PROFILE ──────────────────────────────
            'psychological' => [
                'core_values' => ['الكرامة', 'الوفاء', 'الحب', 'النجاح المهني'],
                'primary_fear' => 'الإحساس بالرخص والاستغلال',
                'ego_level' => 55,   // 0–100 for radar
                'emotional_stability' => 32,   // 0–100 (low = unstable)
                'attachment_style' => 72,   // 0–100 (high = anxious)
                'attachment_label' => 'قلق (Anxious Attachment)',
                'love_language' => 'كلمات التشجيع + الوفاء الكامل',
                'manipulation_tactics' => ['لعب دور الضحية', 'الصمت العقابي', 'قلب الطاولة'],
                'guilt_trigger' => 'تذكيره بالتضحيات والمواقف اللي وقف فيها جنبها',
                'validation_source' => 'من مدى إخلاص الطرف الآخر — ومن نجاحه المهني',
                'stress_response' => 'هجوم مباشر أو بلوك فوري',
                'cognitive_biases' => ['التفكير الثنائي (أبيض أو أسود)', 'الإسقاط العاطفي'],
            ],

            // ── 6. EMOTIONAL DYNAMICS ────────────────────────────────
            'emotional' => [
                'hedra_side' => [
                    'love_expressions' => ['"إنتي الوحيدة"', '"من غيرك مش هينفع"', '"البلوزة الصفرا مش هنساها"'],
                    'jealousy_triggers' => ['لما تبعت صورة مع حد تاني', 'لما بتذكر إكسها'],
                    'red_flags' => ['الكذب', 'التحوير', 'الرخص العلني'],
                    'green_flags' => ['الاعتراف بالغلط بسرعة', 'الدموع الحقيقية', 'الاتصال المبادر'],
                    'apology_pattern' => 'بيتراجع بعد فترة صمت لما يحس بالذنب',
                ],
                'contact_side' => [
                    'love_expressions' => ['"حقك فوق راسي"', '"بعتذر"', '"إنت عارف إني بحبك"'],
                    'jealousy_triggers' => ['لما بيتكلم عن بنات تانية', 'لما بيتجاهلها'],
                    'manipulation_instances' => ['بعتت صورة عشان تشوف رد فعله', 'الصمت لساعات', 'لعب الضحية'],
                    'apology_pattern' => 'اعتذار سريع لما تحس إنه هيقفل الباب نهائياً',
                ],
                'shared' => [
                    'major_conflicts' => ['خناقة صورة الماسنجر', 'مكالمة الفجر', 'سفرية الغردقة'],
                    'conflict_resolution' => 'مارلين بتبادر بالاعتذار — هدرا بيتجاوز ببطء',
                    'marriage_discussions' => 'اتكلموا عنه — هو قال "السنة دي" — وما حصلش',
                    'future_plans' => ['سفر سوا', 'بيت مشترك', 'مشروع مشترك'],
                    'breakup_threats' => 'من الطرفين — هو بيبلوك — هي بتقول "مش هكلمك تاني"',
                    'trust_issues' => 'مشكلة "التحوير" وادعاء الخيانة الأساسية',
                    'physical_meetings' => 'قابلوا في الغردقة وفي القاهرة — اللقاءات كانت عاطفية جداً',
                ],
            ],

            // ── 7. PACTS & COMMITMENTS ───────────────────────────────
            'pacts' => [
                'silent_pacts' => ['محدش يقرب لحد تاني', 'محدش يتكلم عن العلاقة في العلن'],
                'explicit_promises_hedra' => ['"هتجوزك السنة دي"', '"مش هسيبك"'],
                'explicit_promises_contact' => ['"مش هكلم حد غيرك"', '"إنت حياتي"'],
                'ultimatums_given' => ['هو: "لو بعتي صورة تانية خلاص"', 'هي: "لو بلوك تاني مش هرجع"'],
                'boundaries_set' => ['عدم ذكر الإكسات', 'عدم الاختلاط بناس معينة'],
                'broken_promises' => ['وعده بالزواج اللي اتأجل', 'وعدها بعدم البلوك اللي اتكسر'],
                'forgiveness_moments' => ['بعد خناقة الصورة — رجعوا وتكلموا لساعات', 'بعد سفرية الغردقة'],
                'unfinished_business' => ['موضوع الجواز اللي لسه معلق', 'الأمانة اللي اتكلموا فيها'],
            ],

            // ── 8. SOCIAL GRAPH ──────────────────────────────────────
            'graph' => [
                'persons_mentioned' => [
                    ['name' => 'وفاء',  'relation' => 'صديقة مارلين المقربة'],
                    ['name' => 'هاني',  'relation' => 'شخص تحدث عنه وأثار غيرة هدرا'],
                    ['name' => 'أشرف',  'relation' => 'صديق هدرا المقرب'],
                    ['name' => 'هايدي', 'relation' => 'معرفة مشتركة'],
                ],
                'places_mentioned' => [
                    ['place' => 'الغردقة',    'context' => 'سفرية رومانسية — نقطة تحول'],
                    ['place' => 'الإسكندرية', 'context' => 'بلد مارلين الأصلي'],
                    ['place' => 'مدينة نصر',  'context' => 'حي هدرا'],
                ],
                'timeline_milestones' => [
                    ['date' => '2024-03', 'event' => 'أول تواصل'],
                    ['date' => '2024-07', 'event' => 'أول لقاء الغردقة'],
                    ['date' => '2025-01', 'event' => 'أول بلوك — سبب صورة الماسنجر'],
                    ['date' => '2025-06', 'event' => 'محاولة الرجوع الحالية'],
                ],
                'sentiment_timeline' => [
                    ['week' => 'W1', 'score' => 75],  ['week' => 'W2', 'score' => 80],
                    ['week' => 'W3', 'score' => 60],  ['week' => 'W4', 'score' => 30],
                    ['week' => 'W5', 'score' => 20],  ['week' => 'W6', 'score' => 65],
                    ['week' => 'W7', 'score' => 55],  ['week' => 'W8', 'score' => 40],
                ],
                'power_dynamic' => 'هدرا في موقع القاضي — مارلين في موقع المتهم',
                'relationship_cycle' => ['تواصل مرح', 'إثارة غيرة', 'انفجار', 'اعتذار', 'بلوك', 'فك بلوك'],
            ],

            // ── 9. AI AGENT RULES ────────────────────────────────────
            'ai_rules' => [
                'should_agent_initiate' => 'IF_TRIGGER',
                'initiation_triggers' => ['بعد 3 أيام سكوت', 'في المناسبات', 'لو هي بعتت أول'],
                'forbidden_topics' => ['ذكر الإكسات بالاسم', 'موضوع الغردقة بشكل مباشر', 'سؤال عن الجواز'],
                'recommended_tone' => 'رومانسي + جاد — مع لمسة سخرية خفيفة',
                'conversation_deepness' => 'عميق — أحياناً فلسفي',
                'relationship_phase' => 'محاولة رجوع بعد انهيار',
            ],

            // ── 10. LIFESTYLE & PREFERENCES ──────────────────────────
            'lifestyle' => [
                'favorite_music' => ['أوكا وأورتيجا', 'تامر حسني', 'أغاني الزمن الجميل'],
                'favorite_colors' => ['الأسود', 'الرمادي', 'الكحلي'],
                'food_preferences' => ['مأكولات بحرية', 'حاتة من كل حاجة — مش نافر'],
                'daily_routine' => 'يصحى متأخر، يشتغل في المنزل، يسهر كتير',
                'hobbies' => ['برمجة', 'ألعاب فيديو', 'متابعة كورة'],
                'sleep_pattern' => 'كائن ليلي — بينام بعد 3 ص',
                'digital_habits' => ['واتساب', 'يوتيوب', 'تيليجرام'],
                'health_issues' => 'بيدخن سجائر — ذكر ضغوط مادية',
                'financial_habits' => 'يدبر نفسه — عنده توتر من ضغوط المصاريف',
                'pet_preferences' => 'محايد',
                'favorite_places' => ['الغردقة', 'الساحل', 'الشاليهات'],
            ],

            // ── 11. SOCIAL GRAPH & INFLUENCE ─────────────────────────
            'social' => [
                'family_dynamics' => 'علاقة والده متوترة — قريب من أمه — أختوه تأثير عليه',
                'key_influencers' => ['أشرف — صديقه المقرب', 'أمه — تأثير قيمي'],
                'toxic_relationships' => 'علاقة سابقة تركت أثر عميق في موضوع الثقة',
                'social_status_pursuit' => 'متوسط — بيهتم بصورته لكن مش مهووس',
                'loyalty_tests_performed' => 'طلب منها عدم التواصل مع أشخاص بعينهم واختبر ذلك',
                'public_vs_private_persona' => 'قدام الناس: هادي ومتوازن. معها: حاد وعاطفي جداً',
                'social_anxieties' => 'خايف يبان "رخيص" أو "ضعيف" قدام الناس',
            ],

            // ── 12. SUBCONSCIOUS LAYER (Locked by default) ───────────
            'subconscious' => [
                'defense_mechanisms' => 'الإسقاط: "إنت اللي استفزيتني" — التبرير بالدين والقدر',
                'cognitive_dissonance' => 'بيقول "بحبك" وبيعمل بلوك في نفس الوقت — الفجوة ده صراعه الداخلي',
                'emotional_cycle_pattern' => 'هدوء → توتر → انفجار → اعتذار → شهر عسل → هدوء',
                'hidden_needs_unspoken' => 'محتاج ضمان الولاء الكامل ومش قادر يقوله صراحة',
                'power_struggle_tactics' => 'البلوك كأداة سيطرة — الصمت لفرض الإرادة',
                'blind_spots' => 'مش شايف إن متطلباته العاطفية مستحيلة أحياناً',
                'attachment_trigger_points' => 'لما هي بتبكي حقيقي أو بتذكر لحظة الغردقة — بيتلين فوراً',
                'subconscious_bias_hedra' => 'هي شايفاه "المنقذ اللي ممكن يتحول عدو" — الخانة دي بتخليها تتأرجح',
                'manipulation_loop' => 'إثارة الغيرة → انفجار هدرا → اعتذارها → عودة السيطرة ليها',
                'truth_vs_mask_ratio' => [
                    'mask' => 65,   // % of public persona
                    'truth' => 35,   // % of authentic self shown
                    'analysis' => 'قناعها الأساسي: البنت النظيفة الواثقة — حقيقتها: خايفة ومحتاجة تأكيد مستمر',
                ],
            ],

        ];

        return view('hubs.contact-studio', compact('contact', 'profile'));
    }

    public function contactStudioEg(int $id)
    {
        $contact = Contact::findOrFail($id);

        // Fetch massive 5x expanded mock profile via dedicated service
        $profileEg = ContactStudioEgMock::getProfile($contact);

        return view('hubs.contact-studio-eg', compact('contact', 'profileEg'));
    }

    public function contactWarRoom(int $id)
    {
        $contact = Contact::findOrFail($id);
        $warRoom = WarRoomMock::getProfile($contact);

        return view('hubs.contact-war-room', compact('contact', 'warRoom'));
    }

    public function contactArchives(int $id)
    {
        $contact = Contact::findOrFail($id);
        $archives = GrandArchivesMock::getProfile($contact);

        return view('hubs.contact-archives', compact('contact', 'archives'));
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'role' => 'nullable|string',
            'company' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);

        return response()->json(['success' => true, 'contact' => $contact]);
    }

    public function agents()
    {
        $agents = Agent::all();

        return view('hubs.agents', compact('agents'));
    }

    public function storeAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'role' => 'required|string',
            'model' => 'required|string',
            'system_prompt' => 'nullable|string',
        ]);

        $validated['status'] = 'draft';
        $agent = Agent::create($validated);

        return response()->json(['success' => true, 'agent' => $agent]);
    }

    public function toggleAgent(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        $agent->status = $request->status ?? 'active';
        $agent->save();

        return response()->json(['success' => true]);
    }

    public function memory()
    {
        $memories = Memory::orderBy('created_at', 'desc')->get();

        return view('hubs.memory', compact('memories'));
    }

    public function storeMemory(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'required|string',
            'confidence' => 'nullable|numeric|min:0|max:1',
        ]);

        $memory = new Memory;
        $memory->content = $validated['content'];
        $memory->type = strtolower($validated['type']);
        $memory->source = 'user_injection';
        $memory->title = Str::limit($validated['content'], 40);
        $memory->metadata = [
            'confidence' => (float) ($validated['confidence'] ?? 1.0),
            'injected_by' => 'user',
        ];
        $memory->save();

        return response()->json(['success' => true, 'memory' => $memory]);
    }

    public function logs()
    {
        return view('hubs.logs');
    }

    public function models(Request $request)
    {
        $currentPage = $request->get('page', 1);

        $providersQuery = AIProvider::withCount(['models', 'apiKeys'])
            ->with(['apiKeys' => fn ($q) => $q->where('is_active', true)->limit(1)]);

        $allProviders = $providersQuery->get();

        // Attach usage stats in bulk (single query)
        $monthStats = DB::table('usage_logs')
            ->selectRaw('provider_id, SUM(total_cost) as month_cost, COUNT(*) as month_requests, SUM(input_tokens + output_tokens) as month_tokens')
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfMonth()])
            ->groupBy('provider_id')
            ->get()->keyBy('provider_id');

        $todayStats = DB::table('usage_logs')
            ->selectRaw('provider_id, SUM(total_cost) as today_cost, COUNT(*) as today_requests, SUM(input_tokens + output_tokens) as today_tokens')
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfDay()])
            ->groupBy('provider_id')
            ->get()->keyBy('provider_id');

        // Attach last ping status per provider
        $lastPings = DB::table('provider_health_metrics')
            ->select('provider_id', 'status', 'latency_ms')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('provider_health_metrics')->groupBy('provider_id');
            })->get()->keyBy('provider_id');

        $recentLatencies = DB::table('provider_health_metrics')
            ->select('provider_id', 'latency_ms', 'created_at')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('provider_id');

        // Inject into each provider object before passing to view
        $enrichedProviders = $allProviders->map(function ($p) use ($monthStats, $todayStats, $lastPings, $recentLatencies) {
            $p->month_stats = $monthStats[$p->id] ?? null;
            $p->today_stats = $todayStats[$p->id] ?? null;
            $p->last_ping = $lastPings[$p->id] ?? null;
            $p->health_status = $p->last_ping?->status ?? ($p->is_active ? 'no_ping' : 'disabled');
            $p->recent_latencies = isset($recentLatencies[$p->id]) ? $recentLatencies[$p->id]->pluck('latency_ms')->toArray() : [];

            return $p;
        });

        // Provider Health Summary for strip
        $healthSummary = [
            'active' => $enrichedProviders->where('is_active', true)->count(),
            'total' => $enrichedProviders->count(),
            'no_key' => $enrichedProviders->filter(fn ($p) => $p->api_keys_count === 0)->count(),
            'unreachable' => $enrichedProviders->where('health_status', 'offline')->count(),
            'degraded' => $enrichedProviders->where('health_status', 'degraded')->count(),
            'last_sync_at' => AIProvider::max('last_synced_at'),
        ];

        // Paginate (after enrichment)
        $providers = new LengthAwarePaginator(
            $enrichedProviders->forPage($currentPage, 12),
            $enrichedProviders->count(),
            12,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $modelsQuery = AIModel::with('provider')->get();
        $modelUsageStats = DB::table('usage_logs')
            ->selectRaw('model_id, SUM(total_cost) as month_cost, COUNT(*) as month_requests, SUM(input_tokens + output_tokens) as month_tokens')
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfMonth()])
            ->groupBy('model_id')
            ->get()->keyBy('model_id');

        $models = $modelsQuery->map(function ($m) use ($modelUsageStats) {
            $m->stats = $modelUsageStats[$m->id] ?? null;

            return $m;
        });

        $apiKeysQuery = AIApiKey::with('provider')->get();
        $keyUsageStats = DB::table('usage_logs')
            ->selectRaw('api_key_id, SUM(total_cost) as month_cost, COUNT(*) as month_requests, SUM(input_tokens + output_tokens) as month_tokens')
            ->whereNotNull('api_key_id')
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfMonth()])
            ->groupBy('api_key_id')
            ->get()->keyBy('api_key_id');

        $apiKeys = $apiKeysQuery->map(function ($k) use ($keyUsageStats) {
            $k->stats = $keyUsageStats[$k->id] ?? null;

            return $k;
        });

        $routingRules = IntentRouting::with(['defaultProvider', 'defaultModel', 'fallbackProvider', 'fallbackModel'])->get();

        $logs = DB::table('usage_logs')
            ->leftJoin('ai_providers', 'usage_logs.provider_id', '=', 'ai_providers.id')
            ->leftJoin('ai_models', 'usage_logs.model_id', '=', 'ai_models.id')
            ->select(
                'usage_logs.*',
                'ai_providers.name as provider_name',
                'ai_models.name as model_name'
            )
            ->orderByRaw('COALESCE(usage_logs.timestamp, usage_logs.created_at) DESC')
            ->paginate(15);

        $costByProvider = DB::table('usage_logs')
            ->leftJoin('ai_providers', 'usage_logs.provider_id', '=', 'ai_providers.id')
            ->selectRaw('COALESCE(ai_providers.name, "Unknown") as name, SUM(usage_logs.total_cost) as total_spend')
            ->whereRaw('COALESCE(usage_logs.timestamp, usage_logs.created_at) >= ?', [now()->startOfMonth()])
            ->groupBy('ai_providers.name')
            ->get();

        $costByIntent = DB::table('usage_logs')
            ->selectRaw('COALESCE(intent_name, "general_chat") as intent, SUM(total_cost) as total_spend')
            ->whereRaw('COALESCE(usage_logs.timestamp, usage_logs.created_at) >= ?', [now()->startOfMonth()])
            ->groupBy('intent_name')
            ->get();

        return view('hubs.models', compact(
            'providers',
            'models',
            'apiKeys',
            'routingRules',
            'healthSummary',
            'logs',
            'costByProvider',
            'costByIntent'
        ));
    }

    public function providerDetails($id)
    {
        $provider = AIProvider::with(['models', 'apiKeys'])->findOrFail($id);

        $monthCost = DB::table('usage_logs')
            ->where('provider_id', $provider->id)
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfMonth()])
            ->sum('total_cost');

        $todayCost = DB::table('usage_logs')
            ->where('provider_id', $provider->id)
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfDay()])
            ->sum('total_cost');

        $todayRequests = DB::table('usage_logs')
            ->where('provider_id', $provider->id)
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfDay()])
            ->count();

        $healthMetrics = DB::table('provider_health_metrics')
            ->where('provider_id', $provider->id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $modelStats = DB::table('usage_logs')
            ->selectRaw('model_id, SUM(total_cost) as model_month_cost, COUNT(*) as model_requests, SUM(input_tokens + output_tokens) as model_tokens')
            ->where('provider_id', $provider->id)
            ->whereRaw('COALESCE(timestamp, created_at) >= ?', [now()->startOfMonth()])
            ->groupBy('model_id')
            ->get()->keyBy('model_id');

        foreach ($provider->models as $m) {
            $m->stats = $modelStats[$m->id] ?? null;
        }

        $provider->month_cost = $monthCost;
        $provider->today_cost = $todayCost;
        $provider->today_requests = $todayRequests;
        $provider->health_metrics = $healthMetrics;

        return view('hubs.provider-details', compact('provider'));
    }

    public function settings()
    {
        // Get settings grouped by their group for dynamic rendering in Blade
        $settings = Setting::all()->groupBy('group');

        return view('hubs.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $cacheService = app(SettingCacheService::class);
        $updatedKeys = [];

        // Expecting data in a structured format: { 'key': 'value' }
        $data = $request->all();

        // Remove Laravel tokens and metadata
        unset($data['_token'], $data['_method']);

        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                // Handle type casting for the value
                if ($setting->type === Setting::TYPE_BOOLEAN) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif ($setting->type === Setting::TYPE_INTEGER) {
                    $value = (int) $value;
                } elseif ($setting->type === Setting::TYPE_JSON && is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
                $updatedKeys[] = $key;

                try {
                    $cacheService->forget($key);
                } catch (\Exception $e) {
                    // ignore cache failures
                }
            }
        }

        return response()->json([
            'success' => true,
            'updated_count' => count($updatedKeys),
            'updated_keys' => $updatedKeys,
        ]);
    }

    public function clearSettingsCache()
    {
        $cacheService = app(SettingCacheService::class);
        $cacheService->clear();

        return response()->json([
            'success' => true,
            'message' => 'Settings cache cleared successfully!',
        ]);
    }

    public function peopleConnect(Request $request)
    {
        $selectedChatId = $request->query('conversation_id');

        $conversations = PeopleConnectConversation::with('contact')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $activeAgent = Agent::where('is_active', true)->orderBy('id', 'desc')->first()
            ?? Agent::first()
            ?? new Agent(['name' => 'Souly AI Engine']);

        $activeProviders = AIProvider::where('is_active', true)->pluck('name');
        $fallbackChain = $activeProviders->filter(fn ($name) => in_array($name, ['Google Gemini', 'OpenAI', 'Anthropic', 'DeepSeek', 'Groq', 'Mistral AI', 'Perplexity AI']))->take(3)->implode(' → ');
        if (empty($fallbackChain)) {
            $fallbackChain = $activeProviders->take(3)->implode(' → ') ?: 'OpenAI → Gemini → Anthropic';
        }

        $totalKeys = AIApiKey::count();
        $totalMessages = PeopleConnectMessage::count();
        $totalConversations = $conversations->count();

        $stats = [
            'active_agent_name' => $activeAgent->name,
            'fallback_chain' => $fallbackChain,
            'api_keys_pool_status' => $totalKeys > 0 ? "{$totalKeys} Keys Monitored" : 'Pool Active & Protected',
            'pipeline_status' => "{$totalConversations} Chats / {$totalMessages} Msgs",
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
        ];

        return view('hubs.people-connect', compact('selectedChatId', 'conversations', 'stats'));
    }

    public function syncHermesData($activeSessionId = null)
    {
        $apiUrl = Setting::where('key', 'hermes.api_url')->value('value') ?? 'http://162.243.58.33:8642/v1';
        $apiKey = Setting::where('key', 'hermes.api_key')->value('value') ?? 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27';
        $baseUrl = preg_replace('/\/v1$/i', '', $apiUrl);

        try {
            // 1. Sync Sessions from Hermes API
            $response = Http::timeout(5)->withToken($apiKey)->get($baseUrl.'/api/sessions?limit=500');
            if ($response->successful()) {
                $json = $response->json();
                $sessionsData = $json['items'] ?? $json['data'] ?? (is_array($json) ? $json : []);
                if (is_array($sessionsData)) {
                    $incomingIds = collect($sessionsData)->pluck('id')->filter()->map(fn ($id) => (string) $id)->toArray();
                    $existingSessions = HermesSession::whereIn('id', $incomingIds)->pluck('last_active', 'id')->toArray();

                    $sessionsToInsert = [];
                    foreach ($sessionsData as $s) {
                        if (! is_array($s) || ! isset($s['id'])) {
                            continue;
                        }
                        $id = (string) $s['id'];
                        $apiLastActive = isset($s['last_active']) ? Carbon::createFromTimestamp($s['last_active'])->toDateTimeString() : null;

                        if (! array_key_exists($id, $existingSessions)) {
                            // New Session
                            $sessionsToInsert[] = [
                                'id' => $id,
                                'user_id' => $s['user_id'] ?? null,
                                'title' => $s['title'] ?? $id,
                                'source' => $s['source'] ?? 'api',
                                'model' => $s['model'] ?? null,
                                'started_at' => isset($s['started_at']) ? Carbon::createFromTimestamp($s['started_at'])->toDateTimeString() : null,
                                'ended_at' => isset($s['ended_at']) ? Carbon::createFromTimestamp($s['ended_at'])->toDateTimeString() : null,
                                'end_reason' => $s['end_reason'] ?? null,
                                'message_count' => $s['message_count'] ?? 0,
                                'tool_call_count' => $s['tool_call_count'] ?? 0,
                                'input_tokens' => $s['input_tokens'] ?? 0,
                                'output_tokens' => $s['output_tokens'] ?? 0,
                                'preview' => $s['preview'] ?? null,
                                'last_active' => $apiLastActive,
                                'pinned' => $s['pinned'] ?? false,
                                'archived' => $s['archived'] ?? false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            // Exists, update only if last_active changed
                            $localLastActive = $existingSessions[$id] ? Carbon::parse($existingSessions[$id])->toDateTimeString() : null;
                            if ($apiLastActive && $apiLastActive !== $localLastActive) {
                                HermesSession::where('id', $id)->update([
                                    'last_active' => $apiLastActive,
                                    'message_count' => $s['message_count'] ?? 0,
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                    if (! empty($sessionsToInsert)) {
                        foreach (array_chunk($sessionsToInsert, 100) as $chunk) {
                            HermesSession::insert($chunk);
                        }
                    }
                }
            }

            // 2. Sync Messages for the Active/Selected Session
            $currentSessionId = $activeSessionId ?? Setting::where('key', 'hermes.current_session_id')->value('value');
            if ($currentSessionId) {
                $msgResp = Http::timeout(5)->withToken($apiKey)->get($baseUrl.'/api/sessions/'.$currentSessionId.'/messages');
                if ($msgResp->successful()) {
                    $msgJson = $msgResp->json();
                    $messagesData = $msgJson['data'] ?? $msgJson['messages'] ?? (is_array($msgJson) ? $msgJson : []);
                    if (is_array($messagesData)) {
                        $incomingMsgIds = collect($messagesData)->pluck('id')->filter()->map(fn ($id) => (string) $id)->toArray();
                        $existingMsgIds = HermesMessage::where('hermes_session_id', $currentSessionId)
                            ->whereIn('id', $incomingMsgIds)
                            ->pluck('id')->toArray();

                        $messagesToInsert = [];
                        foreach ($messagesData as $m) {
                            if (! is_array($m) || ! isset($m['id'])) {
                                continue;
                            }
                            $id = (string) $m['id'];

                            if (! in_array($id, $existingMsgIds)) {
                                $messagesToInsert[] = [
                                    'id' => $id,
                                    'hermes_session_id' => (string) $currentSessionId,
                                    'role' => $m['role'] ?? 'user',
                                    'content' => is_array($m['content'] ?? null) ? json_encode($m['content']) : ($m['content'] ?? ''),
                                    'raw_payload' => json_encode($m),
                                    'timestamp' => isset($m['timestamp']) ? Carbon::createFromTimestamp($m['timestamp'])->toDateTimeString() : now()->toDateTimeString(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                        if (! empty($messagesToInsert)) {
                            foreach (array_chunk($messagesToInsert, 100) as $chunk) {
                                HermesMessage::insert($chunk);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Hermes Sync Error: '.$e->getMessage());
        }
    }

    public function hedraSoul(Request $request)
    {
        // Perform sync
        $this->syncHermesData();

        // Hermes API Settings
        $hermesSettings = [
            'api_url' => Setting::firstOrCreate(
                ['key' => 'hermes.api_url'],
                [
                    'value' => 'http://162.243.58.33:8642/v1',
                    'type' => Setting::TYPE_STRING,
                    'group' => 'hermes',
                    'scope' => Setting::SCOPE_GLOBAL,
                    'is_public' => false,
                    'description' => 'Hermes API Base URL',
                ]
            )->value,
            'api_key' => Setting::firstOrCreate(
                ['key' => 'hermes.api_key'],
                [
                    'value' => 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27',
                    'type' => Setting::TYPE_STRING,
                    'group' => 'hermes',
                    'scope' => Setting::SCOPE_GLOBAL,
                    'is_public' => false,
                    'description' => 'Hermes API Key',
                ]
            )->value,
            'default_agent' => Setting::firstOrCreate(
                ['key' => 'hermes.default_agent'],
                [
                    'value' => 'hermes-souly',
                    'type' => Setting::TYPE_STRING,
                    'group' => 'hermes',
                    'scope' => Setting::SCOPE_GLOBAL,
                    'is_public' => false,
                    'description' => 'Hermes Default AI Agent Profile',
                ]
            )->value,
            'current_session_id' => Setting::where('key', 'hermes.current_session_id')->value('value'),
            'current_session_title' => Setting::where('key', 'hermes.current_session_title')->value('value'),
        ];

        $sessions = HermesSession::orderBy('last_active', 'desc')->paginate(10);

        $savedSessionId = $hermesSettings['current_session_id'];
        $selectedSessionId = $request->query('session_id', $savedSessionId);
        $selectedSession = null;

        if ($selectedSessionId) {
            $selectedSession = HermesSession::find($selectedSessionId);
        }

        if (! $selectedSession && $sessions->count() > 0) {
            $selectedSession = $sessions->first();
        }

        $messages = collect();
        if ($selectedSession) {
            // Limit to last 150 messages to prevent memory exhaustion (OOM), then reverse back to chronological order
            $messages = HermesMessage::where('hermes_session_id', $selectedSession->id)
                ->orderBy('created_at', 'desc')
                ->take(150)
                ->get()
                ->reverse()
                ->values();

            if (empty($savedSessionId) || $savedSessionId != $selectedSession->id) {
                Setting::updateOrCreate(
                    ['key' => 'hermes.current_session_id'],
                    ['value' => (string) $selectedSession->id, 'type' => Setting::TYPE_STRING, 'group' => 'hermes', 'scope' => Setting::SCOPE_GLOBAL]
                );
                Setting::updateOrCreate(
                    ['key' => 'hermes.current_session_title'],
                    ['value' => $selectedSession->title ?? $selectedSession->id, 'type' => Setting::TYPE_STRING, 'group' => 'hermes', 'scope' => Setting::SCOPE_GLOBAL]
                );
            }
        }

        $hermesSettings['current_session_id'] = $selectedSession?->id;
        $hermesSettings['current_session_title'] = $selectedSession?->title ?? 'Select Session';

        // Attempt fetching Hermes profiles for initial load
        $availableProfiles = [
            'hermes-souly',
            'sysadmin-33',
            'airecon',
            'kali-pentest',
            'hedra-souly',
        ];
        try {
            $profilesResponse = Http::timeout(3)
                ->withToken($hermesSettings['api_key'])
                ->get(rtrim($hermesSettings['api_url'], '/').'/models');

            if ($profilesResponse->successful()) {
                $modelsData = $profilesResponse->json();
                $fetched = array_map(fn ($item) => $item['id'] ?? $item['name'] ?? $item, $modelsData['data'] ?? []);
                foreach ($fetched as $p) {
                    if (! in_array($p, $availableProfiles)) {
                        $availableProfiles[] = $p;
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback to system agent profiles if unreachable on load
        }

        return view('hubs.hedra-soul', compact('sessions', 'selectedSession', 'messages', 'hermesSettings', 'availableProfiles'));
    }

    public function fetchHermesProfiles(Request $request)
    {
        $apiUrl = rtrim((string) $request->input('api_url', Setting::where('key', 'hermes.api_url')->value('value') ?? 'http://162.243.58.33:8642/v1'), '/');
        $apiKey = (string) $request->input('api_key', Setting::where('key', 'hermes.api_key')->value('value') ?? 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27');

        // Specialized AI Agent profiles configured and operating on system (162.243.58.33)
        $knownProfiles = [
            'hermes-souly',
            'sysadmin-33',
            'airecon',
            'kali-pentest',
            'hedra-souly',
        ];

        try {
            // Fetch agent profiles / models list from Hermes /v1/models endpoint
            $response = Http::timeout(5)
                ->withToken($apiKey)
                ->get($apiUrl.'/models');

            if ($response->successful()) {
                $modelsData = $response->json();
                $fetched = array_map(fn ($item) => $item['id'] ?? $item['name'] ?? $item, $modelsData['data'] ?? []);
                foreach ($fetched as $p) {
                    if (! in_array($p, $knownProfiles)) {
                        $knownProfiles[] = $p;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'profiles' => array_values(array_unique($knownProfiles)),
                'count' => count(array_unique($knownProfiles)),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'profiles' => array_values(array_unique($knownProfiles)),
                'count' => count(array_unique($knownProfiles)),
                'notice' => 'Loaded system agent profiles: '.$e->getMessage(),
            ]);
        }
    }

    public function getHermesHealthDetails(Request $request)
    {
        $apiUrl = rtrim((string) $request->input('api_url', Setting::where('key', 'hermes.api_url')->value('value') ?? 'http://162.243.58.33:8642/v1'), '/');
        $apiKey = (string) $request->input('api_key', Setting::where('key', 'hermes.api_key')->value('value') ?? 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27');

        $healthUrl = preg_replace('/\/v1$/i', '', $apiUrl).'/health/detailed';

        try {
            $response = Http::timeout(5)
                ->withToken($apiKey)
                ->get($healthUrl);

            if ($response->successful()) {
                return response()->json([
                    'connected' => true,
                    'status' => 'ok',
                    'data' => $response->json(),
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            return response()->json([
                'connected' => false,
                'status' => 'error',
                'message' => 'HTTP '.$response->status().': Hermes API health returned unsuccessful response.',
                'timestamp' => now()->toIso8601String(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'status' => 'disconnected',
                'message' => 'Connection error: '.$e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    public function fetchHermesSessions(Request $request)
    {
        $this->syncHermesData();

        $limit = (int) $request->input('limit', 10);
        $activeSessionId = Setting::where('key', 'hermes.current_session_id')->value('value');

        $paginated = HermesSession::orderBy('last_active', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'sessions' => collect($paginated->items())->map(function ($s) use ($activeSessionId) {
                return [
                    'id' => $s->id,
                    'title' => $s->title ?? $s->id,
                    'status' => $s->archived ? 'archived' : 'active',
                    'topic' => $s->source ?? 'api',
                    'task_count' => $s->message_count ?? 0,
                    'last_autonomy_mode' => $s->model ?? 'agent',
                    'summary' => $s->preview ?? 'No summary available.',
                    'updated_at_human' => $s->last_active ? $s->last_active->diffForHumans() : ($s->updated_at ? $s->updated_at->diffForHumans() : 'Just now'),
                    'is_active' => (string) $s->id === (string) $activeSessionId,
                ];
            }),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function getHermesSessionMessages($sessionId)
    {
        // Only sync messages for this session — avoids triggering full session list sync
        $this->syncSessionMessagesOnly($sessionId);

        $messages = HermesMessage::where('hermes_session_id', $sessionId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'messages' => $messages->map(function ($m) {
                return [
                    'id' => $m->id,
                    'role' => $m->role,
                    'content' => $m->content,
                    'raw_payload' => $m->raw_payload,
                    'timestamp_human' => $m->timestamp ? $m->timestamp->diffForHumans() : $m->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function selectHermesSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'session_title' => 'nullable|string',
        ]);

        $sessionId = $request->input('session_id');
        $sessionTitle = $request->input('session_title', $sessionId);

        Setting::updateOrCreate(
            ['key' => 'hermes.current_session_id'],
            [
                'value' => $sessionId,
                'type' => Setting::TYPE_STRING,
                'group' => 'hermes',
                'scope' => Setting::SCOPE_GLOBAL,
                'description' => 'Hermes Currently Selected Session ID',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'hermes.current_session_title'],
            [
                'value' => $sessionTitle,
                'type' => Setting::TYPE_STRING,
                'group' => 'hermes',
                'scope' => Setting::SCOPE_GLOBAL,
                'description' => 'Hermes Currently Selected Session Title',
            ]
        );

        app(SettingCacheService::class)->forget('hermes.current_session_id');
        app(SettingCacheService::class)->forget('hermes.current_session_title');

        $this->syncHermesData($sessionId);

        return response()->json([
            'success' => true,
            'message' => 'Hermes session switched successfully.',
            'session' => [
                'id' => $sessionId,
                'title' => $sessionTitle,
            ],
        ]);
    }

    public function sendHermesMessage(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string',
            'agent' => 'nullable|string',
        ]);

        $sessionId = $validated['session_id'];
        $message = $validated['message'];

        $apiUrl = Setting::where('key', 'hermes.api_url')->value('value') ?? 'http://162.243.58.33:8642/v1';
        $apiKey = Setting::where('key', 'hermes.api_key')->value('value') ?? 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27';

        try {
            $baseUrl = preg_replace('/\/v1\/?$/', '', rtrim($apiUrl, '/'));
            $endpoint = $baseUrl."/api/sessions/{$sessionId}/chat";

            $response = Http::timeout(90)->withToken($apiKey)->post($endpoint, [
                'input' => $message,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Extract assistant reply directly from the response body
                $replyContent = $responseData['message']['content'] ?? null;
                $replyRole = $responseData['message']['role'] ?? 'assistant';
                $respondedSessionId = $responseData['session_id'] ?? $sessionId;

                // Sync only messages for this specific session (avoids pulling new Hermes-internal sessions)
                $this->syncSessionMessagesOnly($respondedSessionId);

                return response()->json([
                    'success' => true,
                    'reply' => $replyContent,
                    'reply_role' => $replyRole,
                    'session_id' => $respondedSessionId,
                ]);
            }

            return response()->json(['success' => false, 'error' => 'Hermes returned HTTP '.$response->status().': '.$response->body()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Sync only messages for a specific session — without triggering the full sessions list sync.
     * This prevents newly-created Hermes internal/child sessions from polluting the sidebar.
     */
    private function syncSessionMessagesOnly(string $sessionId): void
    {
        $apiUrl = Setting::where('key', 'hermes.api_url')->value('value') ?? 'http://162.243.58.33:8642/v1';
        $apiKey = Setting::where('key', 'hermes.api_key')->value('value') ?? 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27';
        $baseUrl = preg_replace('/\/v1\/?$/i', '', rtrim($apiUrl, '/'));

        try {
            $msgResp = Http::timeout(10)->withToken($apiKey)->get($baseUrl.'/api/sessions/'.$sessionId.'/messages');
            if (! $msgResp->successful()) {
                return;
            }

            $msgJson = $msgResp->json();
            $messagesData = $msgJson['data'] ?? $msgJson['messages'] ?? (is_array($msgJson) ? $msgJson : []);
            if (! is_array($messagesData)) {
                return;
            }

            $incomingIds = collect($messagesData)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->toArray();

            $existingIds = HermesMessage::where('hermes_session_id', $sessionId)
                ->whereIn('id', $incomingIds)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            $toInsert = [];
            foreach ($messagesData as $m) {
                if (! is_array($m) || ! isset($m['id'])) {
                    continue;
                }
                $id = (string) $m['id'];
                if (in_array($id, $existingIds, true)) {
                    continue;
                }

                $toInsert[] = [
                    'id' => $id,
                    'hermes_session_id' => $sessionId,
                    'role' => $m['role'] ?? 'user',
                    'content' => is_array($m['content'] ?? null) ? json_encode($m['content']) : ($m['content'] ?? ''),
                    'raw_payload' => json_encode($m),
                    'timestamp' => isset($m['timestamp']) ? Carbon::createFromTimestamp($m['timestamp'])->toDateTimeString() : now()->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($toInsert, 100) as $chunk) {
                HermesMessage::insert($chunk);
            }
        } catch (\Exception $e) {
            \Log::warning('syncSessionMessagesOnly failed for session '.$sessionId.': '.$e->getMessage());
        }
    }

    public function testHermesConnection(Request $request)
    {
        // Trigger data sync during poll
        $this->syncHermesData();

        $apiUrl = rtrim((string) $request->input('api_url', 'http://162.243.58.33:8642/v1'), '/');
        $apiKey = (string) $request->input('api_key', 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27');

        // Extract base root host URL for health check if /v1 was included
        $healthUrl = preg_replace('/\/v1$/i', '', $apiUrl).'/health';

        try {
            $response = Http::timeout(5)
                ->withToken($apiKey)
                ->get($healthUrl);

            if ($response->successful()) {
                $data = $response->json();

                // Fetch profiles / models list if possible
                $profilesResponse = Http::timeout(5)
                    ->withToken($apiKey)
                    ->get($apiUrl.'/models');

                $profiles = [];
                if ($profilesResponse->successful()) {
                    $modelsData = $profilesResponse->json();
                    $profiles = array_map(fn ($item) => $item['id'] ?? $item['name'] ?? $item, $modelsData['data'] ?? []);
                }

                return response()->json([
                    'success' => true,
                    'status' => 'online',
                    'message' => 'Hermes API Server is reachable and operational.',
                    'health' => $data,
                    'profiles' => $profiles,
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'HTTP '.$response->status().': Unable to reach Hermes health endpoint.',
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'offline',
                'message' => 'Connection failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function saveHermesSettings(Request $request)
    {
        $request->validate([
            'api_url' => 'required|string|url',
            'api_key' => 'required|string',
            'default_agent' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'hermes.api_url'],
            [
                'value' => rtrim($request->input('api_url'), '/'),
                'type' => Setting::TYPE_STRING,
                'group' => 'hermes',
                'scope' => Setting::SCOPE_GLOBAL,
                'description' => 'Hermes API Base URL',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'hermes.api_key'],
            [
                'value' => $request->input('api_key'),
                'type' => Setting::TYPE_STRING,
                'group' => 'hermes',
                'scope' => Setting::SCOPE_GLOBAL,
                'description' => 'Hermes API Key',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'hermes.default_agent'],
            [
                'value' => $request->input('default_agent'),
                'type' => Setting::TYPE_STRING,
                'group' => 'hermes',
                'scope' => Setting::SCOPE_GLOBAL,
                'description' => 'Hermes Default AI Agent Profile',
            ]
        );

        app(SettingCacheService::class)->forget('hermes.api_url');
        app(SettingCacheService::class)->forget('hermes.api_key');
        app(SettingCacheService::class)->forget('hermes.default_agent');

        return response()->json([
            'success' => true,
            'message' => 'Hermes API settings updated successfully!',
        ]);
    }

    public function proactiveAi()
    {
        $triggers = ProactiveTrigger::orderBy('next_run_at', 'asc')->get();
        $logs = NotificationLog::orderBy('created_at', 'desc')->take(10)->get();

        return view('hubs.proactive-ai', compact('triggers', 'logs'));
    }

    public function notifications()
    {
        $notifications = HedrasoulNotification::orderBy('created_at', 'desc')
            ->paginate(50);

        return view('hubs.notifications', compact('notifications'));
    }

    public function notificationsData()
    {
        try {
            $approvals = HedrasoulApprovalRequest::orderBy('created_at', 'desc')
                ->take(20)
                ->get();

            $notifications = HedrasoulNotification::active()
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get();

            $pendingApprovals = $approvals->where('status', 'pending')->count();
            $unreadNotifications = $notifications->where('is_read', false)->count();
            $unreadCount = $pendingApprovals + $unreadNotifications;

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'pending_approvals_count' => $pendingApprovals,
                'unread_notifications_count' => $unreadNotifications,
                'approvals' => $approvals,
                'notifications' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'unread_count' => 0,
                'pending_approvals_count' => 0,
                'unread_notifications_count' => 0,
                'approvals' => [],
                'notifications' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function markNotificationRead($id)
    {
        try {
            if (is_numeric($id)) {
                $notif = HedrasoulNotification::find($id);
                if ($notif) {
                    $notif->update(['is_read' => true]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function respondApproval(Request $request, $id)
    {
        try {
            $approval = HedrasoulApprovalRequest::findOrFail($id);
            $action = strtolower((string) $request->input('action', 'approve'));

            if (! in_array($action, ['approve', 'reject', 'defer'], true)) {
                return response()->json(['success' => false, 'error' => 'Invalid action'], 422);
            }

            $approval->update([
                'status' => $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'deferred'),
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'decision_notes' => $request->input('notes', 'Decided via Notification Hub'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Approval request #{$id} has been {$approval->status}.",
                'approval' => $approval,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function clearNotifications()
    {
        try {
            HedrasoulNotification::where('is_read', false)->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generateTestNotification()
    {
        try {
            $risks = ['low', 'medium', 'high', 'critical'];
            $risk = $risks[array_rand($risks)];

            $approval = HedrasoulApprovalRequest::create([
                'source_type' => 'agent',
                'source_id' => 'agent-'.rand(100, 999),
                'action_description' => 'AI Agent Souly requested permission to execute API key rotation & high-priority database indexing.',
                'inputs' => ['target' => 'OpenAI API Gateway', 'scope' => 'production'],
                'risk_level' => $risk,
                'cost_estimate' => 0.0500,
                'agent_reasoning' => 'Security compliance trigger: Key age exceeded 30-day policy limit.',
                'status' => 'pending',
            ]);

            $types = ['info', 'success', 'warning'];
            $type = $types[array_rand($types)];

            $notification = HedrasoulNotification::create([
                'notification_type' => $type,
                'priority' => 'high',
                'title' => 'Test Notification #'.rand(100, 999),
                'body' => 'Real-time test alert emitted to verify badge counter, dropdown positioning, and approval cards.',
                'is_read' => false,
                'is_dismissed' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification and approval request generated successfully!',
                'approval' => $approval,
                'notification' => $notification,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function scheduler()
    {
        $schedules = WorkflowSchedule::with('workflow')->orderBy('next_run_at', 'asc')->get();

        return view('hubs.scheduler', compact('schedules'));
    }

    public function apis()
    {
        return view('hubs.apis');
    }

    public function admin()
    {
        return view('hubs.admin');
    }

    public function waha()
    {
        return view('hubs.waha');
    }

    public function triggerWahaSync(Request $request)
    {
        $typeInput = $request->input('type', 'Contacts');
        $processType = $typeInput === 'Messages' ? 'sync_messages' : 'sync_contacts';

        // Create a trackable WahaSyncProcess record
        $process = WahaSyncProcess::create([
            'type' => $processType,
            'status' => 'pending',
            'progress' => 0,
            'started_at' => now(),
        ]);

        if ($typeInput === 'Messages') {
            RealSyncWahaMessagesJob::dispatch($process->id);
        } else {
            RealSyncWahaContactsJob::dispatch($process->id);
        }

        // Create persistent HedrasoulNotification record in database
        HedrasoulNotification::create([
            'notification_type' => 'info',
            'priority' => 'normal',
            'title' => "WAHA {$typeInput} Sync Dispatched",
            'body' => "Synchronization process for WAHA {$typeInput} pipeline is active in Horizon queue.",
            'related_type' => 'waha_sync',
            'related_id' => $process->id,
            'action_buttons' => [
                ['label' => 'View PeopleConnect', 'url' => route('hub.people-connect')],
            ],
            'is_read' => false,
            'is_dismissed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sync process dispatched for {$typeInput}",
            'process_id' => $process->id,
            'process' => $process,
        ]);
    }

    public function sendContactMessage(
        SendPeopleConnectMessageRequest $request,
        SendContactMessageAction $sendAction
    ) {
        try {
            $result = $sendAction->execute($request->validated());

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('Send contact message exception', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function peopleConnectAgentSettings()
    {
        $agent = Agent::where('description', 'like', '%people%connect%')
            ->orWhere('name', 'like', '%Souly%')
            ->orWhere('is_active', true)
            ->first();

        if (! $agent) {
            $agent = Agent::first();
        }

        $providers = AIProvider::with(['models' => function ($q) {
            $q->where('is_active', true);
        }, 'apiKeys'])->get();

        $models = AIModel::with('provider')->where('is_active', true)->get();
        $apiKeys = AIApiKey::with('provider')->orderBy('created_at', 'desc')->get();

        return view('hubs.people-connect-agent-settings', compact('agent', 'providers', 'models', 'apiKeys'));
    }

    public function savePeopleConnectAgentSettings(SaveAgentSettingsRequest $request)
    {
        $validated = $request->validated();
        $agent = Agent::findOrFail($validated['agent_id']);

        $settings = $agent->settings ?? [];
        $settings['temperature'] = (float) $validated['temperature'];
        $settings['max_tokens'] = (int) $validated['max_tokens'];
        $settings['fallback_models'] = array_filter($validated['fallback_models'] ?? []);
        $settings['tools'] = $validated['tools'] ?? [];
        $settings['skills'] = $validated['skills'] ?? [];
        if (! empty($validated['primary_model_id'])) {
            $settings['model_id'] = $validated['primary_model_id'];
        }

        $agent->update([
            'name' => $validated['name'],
            'system_prompt' => $validated['system_prompt'],
            'settings' => $settings,
        ]);

        return redirect()->back()->with('success', 'AI Agent Persona & Fallback configuration updated successfully!');
    }

    public function manageKeyRotation(ManageKeyRotationRequest $request, EncryptedApiKeyStorage $keyStorage)
    {
        $validated = $request->validated();
        $action = $validated['action'];
        $providerId = $validated['provider_id'];

        if ($action === 'add_key') {
            $keyStorage->saveKey($providerId, $validated['api_key'], $validated['key_name'] ?? 'Rotation Key');

            return response()->json(['success' => true, 'message' => 'New API key encrypted and added to rotation pool.']);
        }

        if ($action === 'release_key') {
            $key = AIApiKey::findOrFail($validated['key_id']);
            $key->update(['cooldown_until' => null]);

            return response()->json(['success' => true, 'message' => 'API Key released from cooldown and restored to active rotation pool.']);
        }

        if ($action === 'revoke_key') {
            $keyStorage->deleteKey($validated['key_id']);

            return response()->json(['success' => true, 'message' => 'API Key removed from rotation pool.']);
        }

        if ($action === 'set_cooldown') {
            $minutes = (int) ($validated['cooldown_minutes'] ?? 60);
            $key = AIApiKey::findOrFail($validated['key_id']);
            $key->update(['cooldown_until' => now()->addMinutes($minutes)]);

            return response()->json(['success' => true, 'message' => "Key manually flagged into {$minutes}-minute cooldown."]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid key rotation action.'], 400);
    }

    public function sendHedraMessage(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|integer',
            'content' => 'nullable|string',
            'message' => 'nullable|string',
            'context' => 'nullable|string',
        ]);

        $body = $validated['content'] ?? $validated['message'] ?? '';
        $sessionId = $validated['session_id'] ?? null;

        // Resolve or create a session if it's missing (e.g. from the dashboard)
        if (! $sessionId) {
            $session = HedrasoulSession::where('status', 'active')
                ->orWhere('status', 'open')
                ->orderBy('updated_at', 'desc')
                ->first();
            if (! $session) {
                $session = HedrasoulSession::create([
                    'title' => 'Dashboard Chat Session',
                    'status' => 'active',
                    'last_autonomy_mode' => 'copilot',
                    'opened_at' => now(),
                ]);
            }
            $sessionId = $session->id;
        }

        // Save User Message
        $message = new HedrasoulMessage;
        $message->session_id = $sessionId;
        $message->sender_type = 'user';
        $message->body = $body;
        $message->status = 'sent';
        $message->save();

        // Call LLM using UniversalAiGatewayService
        $replyText = '';
        $tokensUsed = 0;
        try {
            $aiGateway = app('nexus.ai');
            $agent = Agent::where('status', 'active')->first() ?: Agent::first();
            if (! $agent) {
                $model = AIModel::where('status', 'active')->first();
                $agent = new Agent([
                    'name' => 'Souly',
                    'role' => 'Assistant',
                    'model' => $model ? ($model->external_id ?? $model->name) : 'gemini-1.5-flash',
                    'system_prompt' => 'You are Souly, a helpful AI assistant.',
                    'status' => 'active',
                ]);
            }

            $aiResult = $aiGateway->executeWithAgent($agent, [
                'input' => $body,
                'system_prompt' => $agent->system_prompt,
            ]);

            if (! empty($aiResult['text'])) {
                $replyText = $aiResult['text'];
                $tokensUsed = $aiResult['tokens'] ?? 0;
            } else {
                $replyText = 'I processed your request, but received an empty response.';
            }

            // Save actual usage log in usage_logs table
            try {
                \DB::table('usage_logs')->insert([
                    'provider_id' => $agent->ai_provider_id ?? null,
                    'model_id' => $agent->ai_model_id ?? null,
                    'intent_name' => 'agent_execution_'.$agent->id,
                    'input_tokens' => (int) ($tokensUsed * 0.4),
                    'output_tokens' => (int) ($tokensUsed * 0.6),
                    'total_cost' => round($tokensUsed * 0.000002, 6),
                    'timestamp' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $ex) {
            }

        } catch (\Exception $e) {
            \Log::error('AI Console execution failed: '.$e->getMessage());
            $replyText = 'I encountered an issue processing your request: '.$e->getMessage();
        }

        // Save Agent Response
        $reply = new HedrasoulMessage;
        $reply->session_id = $sessionId;
        $reply->sender_type = 'agent';
        $reply->body = $replyText;
        $reply->status = 'delivered';
        $reply->token_count = $tokensUsed;
        $reply->cost_usd = round($tokensUsed * 0.000002, 4);
        $reply->save();

        return response()->json([
            'success' => true,
            'reply' => $reply->body,
            'token_count' => $tokensUsed,
        ]);
    }

    public function toggleFavorite(Request $request, $id, LogService $logService)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $contact = Contact::findOrFail($id);
        $wasFavorite = $contact->isFavoritedBy($user);

        $user->favoriteContacts()->toggle($contact->id);
        $isFavorite = ! $wasFavorite;

        // Structured audit logging via LogService
        $logService->info('Contact favorite flag changed', [
            'channel' => 'contact',
            'type' => 'favorite_toggle',
            'related_id' => $contact->id,
            'related_type' => Contact::class,
            'user_id' => $user->id,
            'before' => $wasFavorite ? 'favorited' : 'unfavorited',
            'after' => $isFavorite ? 'favorited' : 'unfavorited',
            'actor' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Contact added to favorites.' : 'Contact removed from favorites.',
        ]);
    }

    public function logoutWeb(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hub.dashboard');
    }

    public function restartAgent(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        // Set status to active (representing a restarted/re-initialised state)
        $agent->status = 'active';
        $agent->save();

        // Log application event in logs table
        try {
            \DB::table('logs')->insert([
                'level' => 'INFO',
                'channel' => 'system',
                'message' => "Agent '{$agent->name}' successfully restarted.",
                'type' => 'application',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
        }

        // Broadcast AgentStarted event
        try {
            event(new AgentStarted($agent));
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'message' => "Agent '{$agent->name}' restarted successfully.",
        ]);
    }

    public function dashboardHealth(Request $request, NexusDashboardService $service)
    {
        return response()->json($service->getHealthStatus());
    }

    public function dashboardActivityFeed(Request $request, NexusDashboardService $service)
    {
        $limit = $request->query('limit', 20);

        return response()->json($service->getActivityFeed($limit));
    }

    /**
     * Nexus Dev — Antigravity Control dashboard (Blade hub page).
     * No auth (per Hedra 2026-07-14). Renders the glassmorphism/3D/mobile UI.
     */
    public function dev()
    {
        return view('hubs.dev');
    }

    /**
     * Live status for the Nexus Dev dashboard.
     * Tries the headless Antigravity Automation server (localhost:5000) first;
     * if that is not running (Google locks headless CLI outside VS Code), it falls
     * back to real project telemetry (git, Horizon queue, file tree) so the
     * dashboard is useful immediately. Hedra 2026-07-14.
     */
    public function devStatus()
    {
        $status = [
            'server_up' => false,
            'agent' => 'idle',
            'current_task' => null,
            'model' => 'Gemini 3.5 Flash',
            'thinking' => 'medium',
            'uptime' => 0,
            'active_key' => 1,
            'project' => '/www/wwwroot/Nexus/core/Nexus3',
        ];

        // 1) Try the Antigravity Automation REST feed (only live when Hedra opens Antigravity)
        try {
            $ch = curl_init('http://localhost:5000/status');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 2, CURLOPT_CONNECTTIMEOUT => 2]);
            $raw = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200 && $raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $status = array_merge($status, $decoded);
                    $status['server_up'] = true;
                }
            }
        } catch (\Exception $e) {
            // agent server not running — fall through to project telemetry
        }

        // 2) Real project telemetry (always available)
        $root = base_path();
        $status['git'] = [
            'branch' => trim(shell_exec("cd $root && git rev-parse --abbrev-ref HEAD 2>/dev/null") ?: 'unknown'),
            'last' => trim(shell_exec("cd $root && git log -1 --format=%h%d %s 2>/dev/null") ?: ''),
        ];
        $status['horizon'] = trim(shell_exec('pgrep -f "horizon" >/dev/null 2>&1 && echo running || echo stopped')) ?: 'stopped';
        $status['queue'][] = trim(shell_exec('pgrep -f "queue:work" >/dev/null 2>&1 && echo running || echo stopped')) ?: 'stopped';
        $status['composer'] = trim(shell_exec("cd $root && composer -V 2>/dev/null | head -1") ?: '');
        $status['php'] = PHP_VERSION;
        $status['files_count'] = (int) trim(shell_exec("cd $root && find app -name '*.php' 2>/dev/null | wc -l") ?: 0);

        return response()->json($status);
    }

    /**
     * Send a command/prompt to the headless Antigravity agent via the Automation API.
     * Human-in-loop: Souly reviews before any deploy (UI shows "queued").
     * If the agent server is not running, returns a clear "agent offline" so Hedra knows
     * to open Antigravity (Google locks headless CLI outside VS Code).
     */
    public function devCommand(Request $request)
    {
        $command = trim((string) $request->input('command', ''));
        if ($command === '') {
            return response()->json(['success' => false, 'message' => 'Empty command'], 422);
        }

        try {
            $ch = curl_init('http://localhost:5000/send_command');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode(['command' => $command]),
            ]);
            $raw = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200) {
                return response()->json(['success' => true, 'message' => 'Queued to agent', 'echo' => $raw]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Agent server offline. Open Antigravity 2.0 to start the headless agent (Google locks the CLI to VS Code terminal).',
            ], 502);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Agent server unreachable: '.$e->getMessage()], 502);
        }
    }
}
