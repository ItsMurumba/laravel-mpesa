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
            {
                text: 'Security',
                collapsed: true,
                items: [
                    {text: 'Authorization', link: '/security/authorization'},
                ]
            },
            {
                text: 'Payments',
                collapsed: true,
                items: [
                    {text: 'B2B Express Checkout', link: '/payments/b2b-express-checkout'},
                    {text: 'B2C Account Top Up', link: '/payments/b2c-account-top-up'},
                    {text: 'Business Pay Bill', link: '/payments/paybill'},
                    {text: 'Business Buy Goods', link: '/payments/buy-goods'},
                    {text: 'Customer to Business', link: '/payments/c2b'},
                    {text: 'Dynamic QR', link: '/payments/dynamic-qr'},
                    {text: 'Mpesa Express', link: '/payments/mpesa-express'},
                    {text: 'Mpesa Ratiba', link: '/payments/mpesa-ratiba'},
                    {text: 'Tax Remittance', link: '/payments/tax-remittance'},
                ]
            },
            {
                text: 'Disbursements',
                collapsed: true,
                items: [
                    {text: 'Business to Customer', link: '/disbursements/b2c'},
                ]
            },
            {
                text: 'Experience',
                collapsed: true,
                items: [
                    {text: 'Account Balance', link: '/experience/account-balance'},
                    {text: 'Bill Manager', link: '/experience/bill-manager'},
                    {text: 'Transaction Status', link: '/experience/transaction-status'},
                    {text: 'Reversals', link: '/experience/reversals'},
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