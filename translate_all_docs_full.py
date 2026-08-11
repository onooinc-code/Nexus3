import os

eng_dir = '/www/wwwroot/Nexus/core/Nexus3/public/HtmlDemo/docs'
ar_dir = '/www/wwwroot/Nexus/core/Nexus3/public/HtmlDemoArabic/docs'

# Dictionary of phrase replacements for translating markdown structure while leaving code blocks & URLs untouched
replacements = [
    ("# 08. Dynamic WAHA Gateway & Cache Fallback Engine", "# ٠٨. بوابة WAHA الديناميكية ومحرك التخزين المؤقت الاحتياطي"),
    ("# 09. Async Queue Dispatcher & Job Serialization", "# ٠٩. موزع الطوابير غير التزامني وتسلسل المهام"),
    ("# 10. ECA Rules Engine Architecture & Trigger Execution", "# ١٠. معمارية محرك قواعد ECA وتنسيق المحفزات"),
    ("# 11. NLP Intent Classification Stub & Keyword Fallback Pipeline", "# ١١. مصنف النوايا NLP والمسار الاحتياطي للكلمات المفتاحية"),
    ("# 12. Human Handoff, Autopilot Disconnect & Safety Off-Switch", "# ١٢. تسليم التحكم البشري وقاطع الأمان الذاتي"),
    ("# 13. Dynamic Context Assembler & Multi-turn Prompt Engineering", "# ١٣. مجمع السياق الديناميكي وهندسة التوجيهات متعددة الجولات"),
    ("# 14. AI Agent Studio UI Architecture & Real-Time Flow Visualizer", "# ١٤. بنية واجهة استوديو الذكاء الاصطناعي ومراقب التدفقات المباشر"),
    ("# 15. AI Agent Persona, Memory & Prompt Engine", "# ١٥. شخصية الوكيل والذاكرة الديناميكية ومحرك التوجيهات"),
    ("# 16. AI Provider Multi-Key AES-256 Encryption & Rotation Engine", "# ١٦. محرك تشفير وتدوير مفاتيح المزودين بـ AES-256"),
    ("# 17. Studio Mockups vs Real Engine Audit", "# ١٧. تحليل نماذج الواجهة ومطابقتها مع محرك النظام المباشر"),
    ("# 18. Autonomous AI Agent Workforce Roadmap", "# ١٨. خارطة طريق وكلاء الذكاء الاصطناعي الذاتية"),
    ("# 19. Full Relational Database Schema Matrix Reference", "# ١٩. المرجع الكامل لمخطط قاعدة البيانات العلائقية"),
    ("# 20. Architectural Code Matrix & Class Inheritance Hierarchy", "# ٢٠. المرجع الهيكلي لفئات النظام ومصفوفة الأكواد"),
    ("# 21. REST API & Real-Time WebSocket API Specification Reference", "# ٢١. المواصفات المرجعية لنقاط نهاية REST API والـ WebSockets"),
    ("## 1. Architectural", "## ١. التسلسل المعماري لـ"),
    ("## 2. Deep-Dive Source Code Analysis", "## ٢. التحليل العميق للكود المصدري"),
    ("## 3. Database Architecture", "## ٣. بنية قاعدة البيانات والمخطط العلائقي"),
    ("## 4. Summary & Next Steps in Pipeline", "## ٤. الملخص والخطوات التالية في خط الأنابيب"),
    ("## 5. Summary & Next Steps in Pipeline", "## ٥. الملخص والخطوات التالية في خط الأنابيب"),
    ("Column Name", "اسم العمود"),
    ("Type", "النوع"),
    ("Modifiers", "المعدلات"),
    ("Engineering Purpose", "الغرض الهندسي"),
    ("Primary KEY, AUTO_INCREMENT", "PRIMARY KEY, AUTO_INCREMENT (مفتاح رئيسي)"),
    ("NOT NULL, INDEX", "NOT NULL, INDEX (مفهرس)"),
    ("FOREIGN KEY", "FOREIGN KEY (مفتاح أجنبي)"),
]

for filename in sorted(os.listdir(eng_dir)):
    if not filename.endswith('.md'):
        continue
    eng_path = os.path.join(eng_dir, filename)
    ar_path = os.path.join(ar_dir, filename)
    
    # If 01-07 already created with manual high detail, keep or check
    if int(filename.split('-')[0]) <= 7:
        continue

    with open(eng_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Process replacements for common section headers
    for old, new in replacements:
        content = content.replace(old, new)
        
    with open(ar_path, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filename}")
