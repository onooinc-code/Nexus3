<?php

namespace App\Services\Mock;

class GrandArchivesMock
{
    public static function getProfile($contact = null)
    {
        return [
            // 1. Broken Promises
            'broken_promises' => [
                ['date' => '2025-01-10', 'promise' => 'هبطل أعمل بلوك مهما حصل بينا.', 'context' => 'بعد خناقة رأس السنة.'],
                ['date' => '2025-02-14', 'promise' => 'عمري ما هكلم الشخص دا تاني عشان بيضايقك.', 'context' => 'اكتشفت إنها رجعت تكلمه بعدها بأسبوع.'],
            ],

            // 2. Kept Promises
            'kept_promises' => [
                ['date' => '2024-12-01', 'promise' => 'هجيبلك الهدية اللي بتحبها في عيد ميلادك.', 'context' => 'وفّت بوعدها وجابتها.'],
            ],

            // 3. Passive-Aggressive Insults
            'passive_aggressive' => [
                ['date' => '2025-03-01', 'quote' => 'براحتك.. واضح إنك بقيت مشغول بحاجات أهم مني.', 'translation' => 'أنا غاضبة لأنك لا تعطيني الاهتمام الذي أستحقه.'],
                ['date' => '2025-03-10', 'quote' => 'ما شاء الله عليك، بقيت عاقل وبتحسبها صح.', 'translation' => 'إهانة مغلفة لعدم صرفك المال عليها.'],
            ],

            // 4. Fake Apologies
            'fake_apologies' => [
                ['date' => '2025-02-28', 'quote' => 'أنا آسفة لو كلامي ضايقك، بس إنت اللي عصبتني.', 'analysis' => 'تحويل اللوم (Blame Shifting) تحت غطاء الاعتذار.'],
            ],

            // 5. Chronological Hypocrisy
            'chronological_hypocrisy' => [
                ['date1' => 'السبت 01-03', 'statement1' => 'أنا مابحبش الخروج كتير، البيت أحسن.', 'date2' => 'الأحد 02-03', 'statement2' => 'نزلت ستوري وهي في خروجة سهر مع أصحابها.'],
            ],

            // 6. Emotional Blackmail
            'emotional_blackmail' => [
                ['date' => '2025-03-15', 'quote' => 'لو كنت بتحبني بجد مكنتش خلتني أنام زعلانة.', 'tactic' => 'Guilt Tripping'],
            ],

            // 7. Leaked Vulnerabilities
            'leaked_vulnerabilities' => [
                ['date' => '2024-11-20', 'slip' => 'قالت وهي تبكي: "أنا ديماً الناس اللي بحبهم بيسيبوني ويمشوا."', 'strategic_value' => 'هذا هو الجرح الأساسي الذي يحرك كل أفعالها.'],
            ],

            // 8. Avoidance & Retreat
            'avoidance_retreat' => [
                ['date' => '2025-01-25', 'incident' => 'تهربت من مناقشة موضوع الارتباط الرسمي بحجة "مش مستعدة نفسياً".'],
            ],

            // 9. Financial / Opportunistic Moves
            'financial_opportunistic' => [
                ['date' => '2025-02-10', 'incident' => 'افتعلت مشكلة صغيرة قبل موعد دفع قسط معين لتتجنب المساهمة.'],
            ],

            // 10. Triangulation / Jealousy
            'jealousy_triangulation' => [
                ['date' => '2025-03-05', 'incident' => 'ذكرت اسم زميلها في العمل 3 مرات في سياق يبرز اهتمامه بها لإثارة غيرتك.'],
            ],

            // 11. Fabricated Excuses
            'fabricated_excuses' => [
                ['date' => '2025-02-18', 'excuse' => 'نمت غصب عني ومردتش.', 'reality' => 'كانت أونلاين على إنستجرام بعدها بساعتين.'],
            ],

            // 12. Late Confessions
            'late_confessions' => [
                ['date' => '2025-01-05', 'confession' => 'اعترفت إنها كانت بتراقبك من أكونت فيك لمدة 3 شهور قبل ما ترجعوا تتكلموا.'],
            ],

            // 13. Concessions Ledger
            'concessions' => [
                ['date' => '2025-03-12', 'incident' => 'تنازلت عن شرطها بعدم السماح لك بالسفر لوحدك بعدما طبقت الصمت العقابي لمدة يومين.'],
            ],
        ];
    }
}
