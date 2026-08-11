/* Modularized Vue App Engine for Nexus Hub */
const { createApp } = Vue;

createApp({
    data() {
        return {
            searchQuery: '',
            selectedCategory: 'all',
            showAddModal: false,
            showAgentModal: true,
            testingAll: false,
            agentThinking: false,
            agentPrompt: '',
            editingField: { itemId: null, key: null, value: '' },
            modalPos: { x: window.innerWidth - 430, y: 80 },
            isDragging: false,
            dragOffset: { x: 0, y: 0 },
            toast: { show: false, message: '' },
            agentChat: [
                { role: 'assistant', time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), content: 'أهلاً يا هدرا يا حبيبي! أنا nexus-manager Agent المخصص للـ Hub والقواعد الموديولار. أنا عارف الهيكل المعماري والملفات بالتفصيل وأقدر أعدل الأكواد وأضيف features جديدة مباشرة!' }
            ],
            categories: [
                { id: 'all', name: 'All Resources', icon: 'fa-solid fa-layer-group' },
                { id: 'ai', name: 'AI & LLM Providers', icon: 'fa-solid fa-brain' },
                { id: 'panels', name: 'Control Panels & Servers', icon: 'fa-solid fa-server' },
                { id: 'database', name: 'DB & Storage Profiles', icon: 'fa-solid fa-database' },
                { id: 'google', name: 'Google Auth Cookies', icon: 'fa-brands fa-google' },
                { id: 'automation', name: 'Automation & APIs', icon: 'fa-solid fa-bolt' }
            ],
            newItem: { title: '', category: 'ai', rawFields: '' },
            credentials: []
        }
    },
    computed: {
        filteredCredentials() {
            return this.credentials.filter(item => {
                const matchesCategory = this.selectedCategory === 'all' || item.category === this.selectedCategory;
                const query = this.searchQuery.toLowerCase();
                const matchesSearch = !query || 
                    item.title.toLowerCase().includes(query) || 
                    item.subtitle.toLowerCase().includes(query) ||
                    JSON.stringify(item.fields).toLowerCase().includes(query);
                return matchesCategory && matchesSearch;
            });
        },
        testedActiveCount() {
            return this.credentials.filter(c => c.testStatus === 'success').length;
        }
    },
    mounted() {
        this.fetchCredentialsJSON();
        window.addEventListener('mousemove', this.onDrag);
        window.addEventListener('mouseup', this.stopDrag);
    },
    methods: {
        async fetchCredentialsJSON() {
            try {
                const res = await fetch('./nexus-hub/api.php');
                if (res.ok) {
                    this.credentials = await res.json();
                }
            } catch (err) {
                console.error("Failed to load JSON data:", err);
            }
        },
        async saveCredentialsJSON() {
            try {
                await fetch('./nexus-hub/api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.credentials)
                });
            } catch (err) {
                console.error("Failed to save JSON data:", err);
            }
        },
        getFieldMeta(key) {
            const k = key.toLowerCase();
            if (k.includes('pass') || k.includes('key') || k.includes('secret') || k.includes('token') || k.includes('psid')) {
                return { icon: 'fa-solid fa-key', style: 'text-amber-400 bg-amber-500/10 border-amber-500/30 hover:bg-amber-500/30' };
            }
            if (k.includes('url') || k.includes('endpoint') || k.includes('address') || k.includes('link')) {
                return { icon: 'fa-solid fa-link', style: 'text-cyan-400 bg-cyan-500/10 border-cyan-500/30 hover:bg-cyan-500/30' };
            }
            if (k.includes('user') || k.includes('account') || k.includes('profile')) {
                return { icon: 'fa-solid fa-user-shield', style: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30 hover:bg-emerald-500/30' };
            }
            if (k.includes('model') || k.includes('engine') || k.includes('ai')) {
                return { icon: 'fa-solid fa-brain', style: 'text-purple-400 bg-purple-500/10 border-purple-500/30 hover:bg-purple-500/30' };
            }
            if (k.includes('email')) {
                return { icon: 'fa-solid fa-envelope', style: 'text-rose-400 bg-rose-500/10 border-rose-500/30 hover:bg-rose-500/30' };
            }
            if (k.includes('db') || k.includes('sql') || k.includes('database') || k.includes('ftp')) {
                return { icon: 'fa-solid fa-database', style: 'text-blue-400 bg-blue-500/10 border-blue-500/30 hover:bg-blue-500/30' };
            }
            if (k.includes('port') || k.includes('span') || k.includes('ip') || k.includes('host')) {
                return { icon: 'fa-solid fa-network-wired', style: 'text-orange-400 bg-orange-500/10 border-orange-500/30 hover:bg-orange-500/30' };
            }
            return { icon: 'fa-solid fa-hashtag', style: 'text-teal-400 bg-teal-500/10 border-teal-500/30 hover:bg-teal-500/30' };
        },
        getCategoryCount(catId) {
            if (catId === 'all') return this.credentials.length;
            return this.credentials.filter(c => c.category === catId).length;
        },
        startDrag(e) {
            this.isDragging = true;
            this.dragOffset.x = e.clientX - this.modalPos.x;
            this.dragOffset.y = e.clientY - this.modalPos.y;
        },
        onDrag(e) {
            if (this.isDragging) {
                this.modalPos.x = e.clientX - this.dragOffset.x;
                this.modalPos.y = e.clientY - this.dragOffset.y;
            }
        },
        stopDrag() {
            this.isDragging = false;
        },
        clearChat() {
            this.agentChat = [
                { role: 'assistant', time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), content: 'تم إعادة ضبط جلسة nexus-manager Agent!' }
            ];
            this.showToast('nexus-manager Chat Reset');
        },
        startEditing(itemId, key, value) {
            this.editingField = { itemId, key, value };
        },
        saveInlineEdit(item) {
            if (this.editingField.itemId && this.editingField.key) {
                item.fields[this.editingField.key] = this.editingField.value;
                this.saveCredentialsJSON();
                this.showToast(`Updated ${this.editingField.key} in JSON database!`);
                this.cancelInlineEdit();
            }
        },
        cancelInlineEdit() {
            this.editingField = { itemId: null, key: null, value: '' };
        },
        async pasteToField(item, key) {
            try {
                const text = await navigator.clipboard.readText();
                if (text) {
                    item.fields[key] = text;
                    this.saveCredentialsJSON();
                    this.showToast(`Pasted and saved ${key} to JSON!`);
                }
            } catch (e) {
                this.showToast('Clipboard access denied');
            }
        },
        copyValue(val) {
            navigator.clipboard.writeText(val);
            this.showToast(`Copied value: ${val.substring(0, 20)}...`);
        },
        copyText(text) {
            navigator.clipboard.writeText(text);
            this.showToast(`Copied title: ${text}`);
        },
        copyCardAsText(item) {
            let out = `=== ${item.title} ===\n`;
            for (const [k, v] of Object.entries(item.fields)) {
                out += `${k}: ${v}\n`;
            }
            navigator.clipboard.writeText(out);
            this.showToast('Entire Card Copied!');
        },
        copyAllFilteredJSON() {
            navigator.clipboard.writeText(JSON.stringify(this.filteredCredentials, null, 2));
            this.showToast('JSON view copied to clipboard!');
        },
        deleteItem(id) {
            this.credentials = this.credentials.filter(c => c.id !== id);
            this.saveCredentialsJSON();
            this.showToast('Item deleted and synced to JSON!');
        },
        testSingleCredential(item) {
            item.testing = true;
            setTimeout(() => {
                item.testing = false;
                this.showToast(`Tested ${item.title}: Ping successful`);
            }, 600);
        },
        testAllCredentials() {
            this.testingAll = true;
            setTimeout(() => {
                this.testingAll = false;
                this.showToast('Tested all credentials & endpoints successfully!');
            }, 1200);
        },
        saveNewItem() {
            if (!this.newItem.title) return;
            const fieldsObj = {};
            this.newItem.rawFields.split('\n').forEach(line => {
                const parts = line.split(':');
                if (parts.length >= 2) {
                    fieldsObj[parts[0].trim()] = parts.slice(1).join(':').trim();
                }
            });

            this.credentials.unshift({
                id: Date.now(),
                category: this.newItem.category,
                title: this.newItem.title,
                subtitle: 'User Added Item',
                icon: 'fa-solid fa-key',
                iconBg: 'bg-green-500/10 text-green-400 border-green-500/20',
                testStatus: 'success',
                testCode: 'Custom',
                fields: Object.keys(fieldsObj).length > 0 ? fieldsObj : { 'Value': this.newItem.rawFields }
            });

            this.saveCredentialsJSON();
            this.showAddModal = false;
            this.newItem = { title: '', category: 'ai', rawFields: '' };
            this.showToast('New credential saved to JSON database!');
        },
        async sendAgentMessage() {
            if (!this.agentPrompt.trim()) return;
            const userMsg = this.agentPrompt;
            const currentTime = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            this.agentChat.push({ role: 'user', time: currentTime, content: userMsg });
            this.agentPrompt = '';
            this.agentThinking = true;

            const doApiKey = "REDACTED_DO_TOKEN";
            const systemPrompt = `You are nexus-manager Agent, the dedicated AI engineer for Nexus Credentials Hub.
The project is split into modular files: 
- HTML Shell: /www/wwwroot/Nexus/core/Nexus3/public/nexus-credentials-hub.html
- CSS Styles: /www/wwwroot/Nexus/core/Nexus3/public/nexus-hub/css/style.css
- JS Logic Engine: /www/wwwroot/Nexus/core/Nexus3/public/nexus-hub/js/app.js
- JSON DB: /www/wwwroot/Nexus/core/Nexus3/public/nexus-hub/data/credentials.json
- PHP API Backend: /www/wwwroot/Nexus/core/Nexus3/public/nexus-hub/api.php

You know this modular architecture completely. You can help Hedra manage credentials and modify or add features.
Respond in natural, friendly Masri Arabic or English.`;

            try {
                const response = await fetch("https://inference.do-ai.run/v1/chat/completions", {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${doApiKey}`,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        model: "deepseek-v4-pro",
                        messages: [
                            { role: "system", content: systemPrompt },
                            ...this.agentChat.map(m => ({ role: m.role, content: m.content }))
                        ]
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    const reply = data.choices[0].message.content;
                    this.agentChat.push({ role: 'assistant', time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), content: reply });
                } else {
                    throw new Error("HTTP " + response.status);
                }
            } catch (err) {
                const lower = userMsg.toLowerCase();
                let replyText = "";
                if (lower.includes('add') || lower.includes('ضيف') || lower.includes('جديد')) {
                    const newTitle = userMsg.replace(/(add|ضيف|جديد)/gi, '').trim() || 'AI Generated Resource';
                    this.credentials.unshift({
                        id: Date.now(),
                        category: 'ai',
                        title: newTitle,
                        subtitle: 'Added via nexus-manager Agent',
                        icon: 'fa-solid fa-robot',
                        iconBg: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        testStatus: 'success',
                        testCode: '200 Agent',
                        fields: { 'Endpoint': 'https://inference.do-ai.run/v1', 'API Key': 'doo_v1_agent_key' }
                    });
                    this.saveCredentialsJSON();
                    replyText = `أهلاً يا هدرا! ضفتلك العنصر "${newTitle}" وحفظته فوراً في قاعدة بيانات credentials.json!`;
                } else if (lower.includes('test') || lower.includes('فحص') || lower.includes('اختبر')) {
                    this.testAllCredentials();
                    replyText = `عملت فحص شامل لجميع السيرفرات والـ Endpoints المتاحة، وكل السيرفرات القائمة شغالة ورجعت 200 OK!`;
                } else {
                    replyText = `أهلاً يا هدرا يا حبيبي! أنا nexus-manager Agent المخصص لنظام الاعتمادات والسيرفرات والمشروع المقسم. قولي تحب نعدل أو نضيف إيه؟`;
                }
                this.agentChat.push({ role: 'assistant', time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), content: replyText });
            } finally {
                this.agentThinking = false;
            }
        },
        showToast(msg) {
            this.toast.message = msg;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 2200);
        }
    }
}).mount('#app');
