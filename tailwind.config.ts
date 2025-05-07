import type { Config } from 'tailwindcss';

export default {
    content: [
        "./assets/**/*.{js,ts,jsx,tsx,mdx}",
        "./templates/**/*.twig"
    ],
    theme: {
        extend: {
            colors: {
                background: "var(--background)",
                foreground: "var(--foreground)",
            }
        }
    },
    plugins: []
} satisfies Config;
