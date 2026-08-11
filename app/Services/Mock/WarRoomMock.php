<?php

namespace App\Services\Mock;

class WarRoomMock
{
    public static function getProfile($contact = null)
    {
        return [
            // 1. Response Architect
            'response_architect' => [
                'latest_incoming' => 'أنا مش فاضية دلوقتي ومش عارفة هنزل إمتى، لما أفضى هكلمك.',
                'suggested_responses' => [
                    [
                        'type' => 'The Cold Response',
                        'text' => 'تم.',
                        'psych_impact' => 'يزيد من حيرتها وقلقها، يكسر توقعها برد فعل غاضب أو لحوح.',
                    ],
                    [
                        'type' => 'The Gaslight Response',
                        'text' => 'براحتك.. أنا كدا كدا خارج مع صحابي، متأخريش بس عشان عندي شغل الصبح.',
                        'psych_impact' => 'يقلب الطاولة، يجعلها تشعر أنك مشغول عنها وأنها هي التي تحتاج للتواصل.',
                    ],
                    [
                        'type' => 'The Trigger Response',
                        'text' => 'Seen (تجاهل مقصود لمدة 48 ساعة)',
                        'psych_impact' => 'ضربة مدمرة للإيجو، ستجبرها على المبادرة بالسؤال.',
                    ],
                ],
            ],

            // 2. Reality Distortion Field
            'reality_distortion' => [
                'current_argument' => 'تتهمني بأنني لا أهتم بها لأنني نسيت عيد ميلاد قطتها.',
                'inversion_tactic' => 'تحويل التركيز إلى "أولويات العلاقة".',
                'script' => '"أنا بهتم بمستقبلنا وبشغلي عشان أقدر أوفر حياة كريمة لينا، لو شايفة إن اهتمامي بصغائر الأمور أهم من بنائي لنفسي عشانا، يبقى إحنا تفكيرنا مختلف تماماً."',
            ],

            // 3. Burnout Countdown & ELO
            'burnout' => [
                'estimated_days_to_break' => 4,
                'current_tension_level' => 'Critical (88%)',
                'hedra_elo' => 2450,
                'contact_elo' => 1200,
                'win_streak' => 3,
            ],

            // 4. Narcissism Meter & Intentions
            'narcissism_meter' => 92, // %
            'hidden_intentions' => [
                [
                    'said' => 'أنا محتاجة مساحة لنفسي الفترة دي.',
                    'means' => 'أنا أختبر مدى تمسكك بي، إذا ابتعدت سأغضب، وإذا اقتربت سأختنق.',
                ],
                [
                    'said' => 'أنت أحسن حد عرفته في حياتي.',
                    'means' => 'أريد منك خدمة كبيرة قريباً (Love Bombing).',
                ],
            ],

            // 5. Punishment/Reward Matrix
            'punishment_reward' => [
                'when_to_punish' => ['عندما تتأخر في الرد أكثر من 6 ساعات عمداً.', 'عندما تستخدم صيغة الأمر.', 'عند مقارنتك بأي شخص آخر.'],
                'how_to_punish' => ['الرد بكلمة واحدة.', 'إلغاء موعد مفاجئ بحجة العمل.', 'الصمت العقابي المصغر (Micro-Silence).'],
                'when_to_reward' => ['عندما تبادر بالاعتذار الصريح.', 'عندما تظهر ضعفاً حقيقياً وليس تلاعباً.'],
                'how_to_reward' => ['تأكيد الإعجاب اللفظي (Validation).', 'الاستماع المتعاطف لمدة 15 دقيقة فقط.'],
            ],
        ];
    }
}
