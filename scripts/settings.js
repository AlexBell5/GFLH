// Only define if not already defined
if (!window.SimpleScreenReader) {

    window.SimpleScreenReader = class {
        constructor() {
            this.isActive = this.loadSetting('isActive', false);
            this.rate = this.loadSetting('rate', 1);
            this.voices = [];
            this.currentVoice = null;
            this.synth = window.speechSynthesis;

            document.addEventListener('DOMContentLoaded', () => {
                this.loadVoices();
                this.setupControls();
                this.updateUIFromSettings();
                this.initDarkModeToggle();
            });

            if (speechSynthesis.onvoiceschanged !== undefined) {
                speechSynthesis.onvoiceschanged = () => this.loadVoices();
            }
        }

        loadSetting(key, defaultValue) {
            const saved = localStorage.getItem(`screenReader_${key}`);
            if (saved !== null) {
                if (saved === 'true') return true;
                if (saved === 'false') return false;
                const num = parseFloat(saved);
                return isNaN(num) ? saved : num;
            }
            return defaultValue;
        }

        saveSetting(key, value) {
            localStorage.setItem(`screenReader_${key}`, value);
        }

        loadVoices() {
            this.voices = this.synth.getVoices();
            const voiceSelect = document.getElementById('voice-select');
            if (!voiceSelect || this.voices.length === 0) return;

            voiceSelect.innerHTML = '';
            this.voices.forEach((voice, idx) => {
                const option = document.createElement('option');
                option.value = idx;
                option.textContent = voice.name + (voice.default ? ' (default)' : '');
                voiceSelect.appendChild(option);
            });

            const savedVoiceIndex = this.loadSetting('voiceIndex', 0);
            voiceSelect.value = savedVoiceIndex;
            this.currentVoice = this.voices[savedVoiceIndex];
        }

        setupControls() {
            const toggleBtn = document.getElementById('toggle-reader');
            if (toggleBtn) toggleBtn.addEventListener('click', () => this.toggleReader());

            const speedControl = document.getElementById('speed-control');
            if (speedControl) {
                speedControl.value = this.rate;
                speedControl.addEventListener('input', e => {
                    this.rate = parseFloat(e.target.value);
                    this.saveSetting('rate', this.rate);
                });
            }

            const voiceSelect = document.getElementById('voice-select');
            if (voiceSelect) voiceSelect.addEventListener('change', e => {
                const idx = parseInt(e.target.value);
                this.currentVoice = this.voices[idx];
                this.saveSetting('voiceIndex', idx);
                this.speakText("Voice changed");
            });
        }

        updateUIFromSettings() {
            const toggleBtn = document.getElementById('toggle-reader');
            if (toggleBtn) toggleBtn.textContent = this.isActive ? 'Disable Screen Reader' : 'Enable Screen Reader';
        }

        toggleReader() {
            this.isActive = !this.isActive;
            this.saveSetting('isActive', this.isActive);
            this.updateUIFromSettings();
            this.speakText(this.isActive ? 'Screen reader enabled' : 'Screen reader disabled');
        }

        speakText(text) {
            if (!this.isActive || !this.currentVoice) return;
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.voice = this.currentVoice;
            utterance.rate = this.rate;
            this.synth.speak(utterance);
        }

        initDarkModeToggle() {
            const button = document.getElementById('toggle');
            if (!button) return;

            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') document.body.classList.add('dark');

            button.addEventListener('click', () => {
                document.body.classList.toggle('dark');
                const isDark = document.body.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    };

    // Initialize instance
    window.screenReader = new window.SimpleScreenReader();
}

function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}