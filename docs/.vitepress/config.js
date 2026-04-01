import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'LiteQuote',
  description: 'Ultra-lightweight quote request plugin for WooCommerce',
  base: process.env.VITEPRESS_BASE || '/',
  lang: 'en-US',
  head: [
    ['meta', { name: 'theme-color', content: '#0073aa' }],
    ['meta', { property: 'og:title', content: 'LiteQuote Documentation' }],
    ['meta', { property: 'og:description', content: 'Ultra-lightweight quote request plugin for WooCommerce. < 150 KB, zero jQuery.' }],
  ],
  themeConfig: {
    logo: '/logo.svg',
    siteTitle: 'LiteQuote',
    nav: [
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'Features', link: '/features/quote-button' },
      { text: 'Developers', link: '/developers/hooks' },
      { text: 'Changelog', link: '/changelog' },
      {
        text: 'v2.0.0',
        items: [
          { text: 'Changelog', link: '/changelog' },
          { text: 'License', link: '/license' },
        ]
      }
    ],
    sidebar: {
      '/guide/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Introduction', link: '/guide/getting-started' },
            { text: 'Installation', link: '/guide/installation' },
            { text: 'SMTP Setup', link: '/guide/smtp-setup' },
            { text: 'Quick Configuration', link: '/guide/configuration' },
          ]
        }
      ],
      '/features/': [
        {
          text: 'Core Features',
          items: [
            { text: 'Quote Button', link: '/features/quote-button' },
            { text: 'Quote Modal', link: '/features/modal' },
            { text: 'Email Notifications', link: '/features/emails' },
            { text: 'WhatsApp Integration', link: '/features/whatsapp' },
            { text: 'Catalogue Mode', link: '/features/catalogue-mode' },
          ]
        },
        {
          text: 'Pro Features',
          items: [
            { text: 'Quote Dashboard', link: '/features/dashboard' },
            { text: 'Send Quotes with Price', link: '/features/send-quote' },
            { text: 'PDF Generation', link: '/features/pdf' },
            { text: 'CSV Export', link: '/features/csv-export' },
          ]
        },
        {
          text: 'Security',
          items: [
            { text: 'Anti-Spam & Rate Limiting', link: '/features/security' },
          ]
        }
      ],
      '/developers/': [
        {
          text: 'For Developers',
          items: [
            { text: 'Hooks & Filters', link: '/developers/hooks' },
            { text: 'Architecture', link: '/developers/architecture' },
            { text: 'Translation', link: '/developers/translation' },
          ]
        }
      ],
    },
    socialLinks: [
      { icon: 'github', link: 'https://github.com/amazscript/litequote' }
    ],
    footer: {
      message: 'Released under the GPL v2 License.',
      copyright: 'Copyright 2025-2026 AmazScript / ByteSproutLab'
    },
    search: {
      provider: 'local'
    },
    editLink: {
      pattern: 'https://github.com/amazscript/litequote/edit/main/docs/:path',
      text: 'Edit this page on GitHub'
    }
  }
})
