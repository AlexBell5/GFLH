// settings.js
class SimpleScreenReader {
    constructor() {
        this.isActive = this.loadSetting('isActive', false);
        this.synth = window.speechSynthesis;
        this.voices = [];
        this.currentVoice = null;
        this.rate = this.loadSetting('rate', 1);

        this.init();
    }

    // Load a setting from localStorage
    loadSetting(key, defaultValue) {
        try {
            const saved = localStorage.getItem(`screenReader_${key}`);
            if (saved !== null) {
                if (saved === 'true') return true;
                if (saved === 'false') return false;
                const num = parseFloat(saved);
                if (!isNaN(num)) return num;
                return saved;
            }
        } catch (e) { console.error(e); }
        return defaultValue;
    }

    // Save a setting to localStorage
    saveSetting(key, value) {
        try {
            localStorage.setItem(`screenReader_${key}`, value);
        } catch (e) { console.error(e); }
    }

    init() {
        this.loadVoices();
        this.setupControls();
        this.updateUIFromSettings();
        this.initDarkModeToggle();

        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = () => this.loadVoices();
        }
    }

    loadVoices() {
        this.voices = this.synth.getVoices();
        if (this.voices.length > 0) {
            this.currentVoice = this.voices[0];
            const voiceSelect = document.getElementById('voice-select');
            if (voiceSelect) {
                voiceSelect.innerHTML = '';
                this.voices.forEach((voice, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = voice.name + (voice.default ? " (default)" : "");
                    voiceSelect.appendChild(option);
                });

                const savedVoiceIndex = this.loadSetting('voiceIndex', 0);
                voiceSelect.value = savedVoiceIndex;
                this.currentVoice = this.voices[savedVoiceIndex];
            }
        }
    }

    setupControls() {
        const toggleBtn = document.getElementById('toggle-reader');
        if (toggleBtn) toggleBtn.addEventListener('click', () => this.toggleReader());

        const speedControl = document.getElementById('speed-control');
        if (speedControl) speedControl.addEventListener('input', e => {
            this.rate = parseFloat(e.target.value);
            this.saveSetting('rate', this.rate);
        });

        const voiceSelect = document.getElementById('voice-select');
        if (voiceSelect) voiceSelect.addEventListener('change', e => {
            const idx = parseInt(e.target.value);
            this.currentVoice = this.voices[idx];
            this.saveSetting('voiceIndex', idx);
        });
    }

    updateUIFromSettings() {
        const toggleBtn = document.getElementById('toggle-reader');
        if (toggleBtn) toggleBtn.textContent = this.isActive ? 'Disable Screen Reader' : 'Enable Screen Reader';

        const speedControl = document.getElementById('speed-control');
        if (speedControl) speedControl.value = this.rate;
    }

    toggleReader() {
        this.isActive = !this.isActive;
        this.saveSetting('isActive', this.isActive);
        const toggleBtn = document.getElementById('toggle-reader');
        if (toggleBtn) toggleBtn.textContent = this.isActive ? 'Disable Screen Reader' : 'Enable Screen Reader';
        this.speakText(this.isActive ? 'Screen reader enabled' : 'Screen reader disabled');
    }

    speakText(text) {
        if (!this.isActive) return;
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.voice = this.currentVoice;
        utterance.rate = this.rate;
        this.synth.speak(utterance);
    }

    // ---------------- Dark Mode ----------------
    initDarkModeToggle() {
        // Apply dark mode globally if enabled
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }

        const toggle = document.getElementById('dark-mode-toggle');
        if (!toggle) return;

        const slider = toggle.querySelector('.toggle-slider');
        if (slider) slider.style.left = localStorage.getItem('darkMode') === 'true' ? '26px' : '0px';

        toggle.addEventListener('click', () => {
            const isActive = document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isActive);
            if (slider) slider.style.left = isActive ? '26px' : '0px';
        });
    }
}

// ---------------- Initialization ----------------
document.addEventListener('DOMContentLoaded', () => {
    // Apply dark mode to all pages if enabled
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }

    // Initialize screen reader on all pages
    const screenReader = new SimpleScreenReader();
    window.screenReader = screenReader;
});

