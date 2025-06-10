import {defineConfig} from 'vitepress'

export default defineConfig({
    title: "Laravel Mpesa",
    description: "A Laravel package for Mpesa integration",
    lastUpdated: true,
    head: [
        [
            'script',
            { defer: '', 'data-domain': 'mpesa.itsmurumba.dev', src: 'https://st.artisanelevated.com/js/script.js' }
        ],
    ],
    themeConfig: {
        search: {
            provider: 'local'
        },

        nav: [
            {text: 'Home', link: '/'},
            {text: 'Guide', link: '/introduction/getting-started'},
        ],

        sidebar: [
            {
                text: 'Introduction',
                collapsed: false,
                items: [
                    {text: 'Getting Started', link: '/introduction/getting-started'},
                    {text: 'Progress', link: '/introduction/progress'},
                ]
            },
        ],

        socialLinks: [
            {icon: 'github', link: 'https://github.com/itsmurumba/laravel-mpesa'}
        ],

        footer: {
            message: 'MIT License.',
            copyright: 'Copyright © 2025 ItsMurumba'
        }
    }
})