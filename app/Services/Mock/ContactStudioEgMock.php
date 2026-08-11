<?php

namespace App\Services\Mock;

class ContactStudioEgMock
{
    public static function getProfile($contact = null)
    {
        return [
            // ── 1. النواة الشخصية (IDENTITY CORE) ──────────────────────────────────
            'identity' => [
                'full_name' => $contact->name ?? 'أحمد زيدان',
                'nicknames' => [
                    'hedra' => ['يا مبي', 'أيوه إنت', 'يا ابني', 'سيدي الفاضل (بسخرية)'],
                    'contact' => ['هدرا', 'الزعيم', 'أبو الهوايل'],
                ],
                'ego_fragility_index' => 85, // 0-100 (عالي جداً = هش من الداخل رغم التكبر)
                'current_danger_level' => 'مرتفع', // منخفض، متوسط، مرتفع، حرج
                'relationship_status' => 'فترة الهدوء المشحون (الما قبل الانفجار)',
                'core_labels' => ['نرجسية خفية', 'تعلق قلق', 'استجابة هجومية'],
            ],

            // ── 2. غرفة التحقيق وسجل التناقضات (INTERROGATION ROOM) ───────────────
            'interrogation' => [
                'hypocrisy_ledger' => [
                    [
                        'statement' => 'أنا عمري ما هعملك بلوك تاني.',
                        'action' => 'عمل بلوك بعدها بـ 3 أسابيع على أتفه سبب.',
                        'severity' => 'High',
                        'timestamp' => '2025-01-15',
                    ],
                    [
                        'statement' => 'أنا مابيهمنيش رأي الناس.',
                        'action' => 'حذف بوست عشان تعليق واحد استفزه من صاحبه.',
                        'severity' => 'Medium',
                        'timestamp' => '2024-11-20',
                    ],
                    [
                        'statement' => 'مش عايز حاجة من العلاقة دي غير الاحترام.',
                        'action' => 'استخدم الصمت العقابي لإجبارها على الاعتذار رغم إنه الغلطان.',
                        'severity' => 'Critical',
                        'timestamp' => '2025-03-02',
                    ],
                ],
                'deception_matrix' => [
                    'gaslighting_frequency' => 70, // %
                    'blame_shifting' => 90, // %
                    'playing_victim' => 80, // %
                    'common_excuses' => ['الضغوط المادية', 'أنتِ اللي بتعصبيني', 'أنا طبعي كدا'],
                ],
            ],

            // ── 3. خريطة الضعف والاستغلال (EXPLOITATION MAP) ──────────────────────
            'exploitation' => [
                'vulnerabilities' => [
                    'الخوف من الظهور بمظهر "الرخيص" أو المتاح بزيادة.',
                    'الرعب الداخلي من التخلي عنه فجأة (Abandonment Issue).',
                    'الحساسية المفرطة تجاه أي مقارنة برجال آخرين.',
                ],
                'trigger_buttons' => [
                    ['button' => 'التأخر في الرد أكثر من 4 ساعات', 'expected_reaction' => 'انفجار غضب ثم صمت عقابي'],
                    ['button' => 'الحديث ببرود واستخدام "أوك"', 'expected_reaction' => 'استفزاز محاولة إثبات الأهمية'],
                    ['button' => 'النجاح المهني للطرف الآخر بدون مساعدته', 'expected_reaction' => 'التقليل من الإنجاز أو إظهار عدم الاكتراث'],
                ],
                'manipulation_tactics_used' => [
                    ['tactic' => 'الفتات العاطفي (Breadcrumbing)', 'counter_tactic' => 'الانسحاب التكتيكي دون عتاب'],
                    ['tactic' => 'التثليث (Triangulation - إثارة الغيرة)', 'counter_tactic' => 'التجاهل المطلق وعدم إظهار أي رد فعل'],
                ],
            ],

            // ── 4. توقعات الذكاء الاصطناعي (PREDICTIVE AI) ─────────────────────────
            'predictive_ai' => [
                'churn_probability' => 65, // احتمالية الانفصال النهائي
                'next_move_forecast' => 'من المتوقع أن يفتعل مشكلة صغيرة خلال الأسبوع القادم لتفريغ شحنة الغضب المكبوتة.',
                'scenario_simulator' => [
                    [
                        'if_hedra_does' => 'تجاهل الرسالة الأخيرة وتركها "Seen"',
                        'contact_reaction' => 'إرسال رسالة عتاب بعد 12 ساعة بنسبة 80%، أو حذفها بنسبة 20%.',
                        'emotional_cost' => 'High',
                    ],
                    [
                        'if_hedra_does' => 'إرسال ميم (Meme) مضحك بدون أي سياق',
                        'contact_reaction' => 'كسر الجليد فورا والرد بضحك بنسبة 95%.',
                        'emotional_cost' => 'Low',
                    ],
                ],
            ],

            // ── 5. التحليل اللغوي والصمت العقابي (LINGUISTIC ANALYSIS) ─────────────
            'linguistics' => [
                'toxic_vocabulary' => [
                    'كلمات التملص' => ['ممكن', 'هنشوف', 'ربنا يسهل', 'أنا قولتلك اللي عندي'],
                    'كلمات التقليل' => ['أنتِ مكبرة الموضوع', 'أوفر أوي', 'كالعادة'],
                ],
                'silence_intervals' => [
                    'average_silent_treatment' => '48 ساعة',
                    'longest_silence' => '14 يوماً (بعد خناقة الغردقة)',
                ],
                'passive_aggressive_ratio' => 75, // %
                'communication_style' => 'مراوغ، يستخدم الهجوم كأفضل وسيلة للدفاع، يميل لاستخدام الـ Voice notes عند الغضب الشديد فقط.',
            ],

            // ── 6. الخريطة الحرارية للمشاعر (EMOTIONAL HEATMAP) ───────────────────
            'emotional_heatmap' => [
                'peak_vulnerability_hours' => ['02:00 AM', '04:00 AM'],
                'peak_anger_days' => ['يوم الأحد (بداية الأسبوع وضغط العمل)', 'يوم الخميس (توقعات عالية للعطلة)'],
                'weekly_mood_swings' => [
                    'Monday' => 40, 'Tuesday' => 45, 'Wednesday' => 30, // 0 = حزين/غاضب, 100 = سعيد
                    'Thursday' => 70, 'Friday' => 80, 'Saturday' => 50, 'Sunday' => 20,
                ],
            ],

            // ── 7. مصفوفة الارتباط والاحتياج (ATTACHMENT MATRIX) ──────────────────
            'attachment' => [
                'attachment_style' => 'تعلق تجنبي خائف (Fearful-Avoidant)',
                'emotional_hunger_scale' => 88, // 100 = محتاج بشدة للاهتمام لكنه يكابر
                'intimacy_fear_level' => 75, // الخوف من القرب الحقيقي
                'validation_needs' => [
                    'يحتاج للتأكيد على رجولته وقراراته باستمرار.',
                    'يحتاج لأن يشعر أنه الطرف الأقوى والمهيمن في العلاقة.',
                ],
            ],

            // ── 8. محاكي الصراعات وتاريخ المعارك (CONFLICT SIMULATOR) ──────────────
            'conflicts' => [
                'historical_battles' => [
                    [
                        'name' => 'معركة صورة الماسنجر',
                        'date' => 'يناير 2025',
                        'victor' => 'هدرا (بالانسحاب وفرض الأمر الواقع)',
                        'emotional_casualties' => 'فقدان الثقة بنسبة 30% من طرف مارلين',
                        'resolution' => 'اعتذار مارلين لإنهاء الخلاف',
                    ],
                    [
                        'name' => 'أزمة مكالمة الفجر',
                        'date' => 'مارس 2025',
                        'victor' => 'مارلين (بكشف الحقيقة)',
                        'emotional_casualties' => 'انهيار كبرياء هدرا المؤقت',
                        'resolution' => 'تجاهل الموضوع وتخطيه وكأنه لم يحدث',
                    ],
                ],
                'typical_fight_duration' => '3 إلى 5 أيام',
                'reconciliation_initiator' => 'دائماً الطرف الآخر، هو لا يعتذر مباشرة أبداً.',
            ],

            // ── 9. أوراق الضغط المادية والاجتماعية (FINANCIAL & SOCIAL LEVERAGE) ───
            'leverage' => [
                'financial_status' => 'متوتر (تحت ضغط الديون ومصاريف المعيشة)',
                'social_capital' => 'منخفض (دائرته مغلقة، يعتمد على شخصين فقط)',
                'leverage_points_for_hedra' => [
                    'الاستقرار المادي (إذا أظهر هدرا استقراراً مادياً، يزيد احترامه/خوفه)',
                    'العلاقات الاجتماعية (يغار من شبكة علاقات هدرا الواسعة)',
                ],
                'leverage_points_against_hedra' => [
                    'المعرفة العميقة بأسرار عائلته، يمكن استخدامها ضده نفسياً.',
                ],
            ],

            // ── 10. القبو الباطني المتقدم (DEEP SUBCONSCIOUS) ─────────────────────
            'subconscious' => [
                'the_naked_truth' => 'من الداخل، هو طفل مرعوب من فكرة أن يتم التخلي عنه كما حدث في طفولته. يستخدم القسوة والبرود كدرع واقٍ ليسبق الطرف الآخر بخطوة الهجر.',
                'core_wound' => 'الجرح النرجسي (Narcissistic Wound) ناتج عن إهانة قديمة من شخصية ذات سلطة (غالباً والده).',
                'shadow_self' => 'الجانب المظلم: يتلذذ برؤية الطرف الآخر يتعذب في انتظاره، لأن هذا يغذي شعوره بالأهمية والقوة المفقودة في حياته الحقيقية.',
                'redemption_possibility' => 'شبه مستحيلة بدون تدخل علاجي نفسي متخصص، الدائرة ستستمر في التكرار (دورة الاستغلال النرجسي).',
            ],
        ];
    }
}
