import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import {
    createIcons,
    LayoutDashboard,
    Users,
    GraduationCap,
    School,
    BookOpen,
    CalendarDays,
    CalendarRange,
    NotebookPen,
    ClipboardCheck,
    StickyNote,
    Calculator,
    ChartColumn,
    Settings,
    CircleUser,
    LogOut,
    Menu,
    X,
    Plus,
    Search,
    Pencil,
    Trash2,
    Eye,
    Download,
    CircleCheck,
    TriangleAlert,
    Info,
    Inbox,
    Sun,
    Moon,
} from 'lucide';

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family = "Inter, 'Segoe UI', sans-serif";

function themeValue(name, fallback) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
}

function paintCharts() {
    const text = themeValue('--muted', '#98989D');
    const grid = themeValue('--line', '#2C2C2E');
    const primary = themeValue('--primary', '#00E5FF');
    const mint = themeValue('--mint', '#32D74B');

    Chart.defaults.color = text;
    Chart.defaults.borderColor = grid;

    document.querySelectorAll('canvas').forEach((canvas) => {
        const chart = Chart.getChart(canvas);
        if (!chart) {
            return;
        }

        if (chart.options.plugins?.legend?.labels) {
            chart.options.plugins.legend.labels.color = text;
        }

        ['x', 'y'].forEach((axis) => {
            const scale = chart.options.scales?.[axis];
            if (!scale) {
                return;
            }
            scale.grid = { ...(scale.grid || {}), color: grid };
            scale.ticks = { ...(scale.ticks || {}), color: text };
        });

        if (chart.config.type === 'line') {
            chart.data.datasets.forEach((dataset) => {
                dataset.borderColor = primary;
                dataset.backgroundColor = `color-mix(in srgb, ${mint} 22%, transparent)`;
            });
        }

        chart.update();
    });
}

function applyTheme(theme) {
    const next = theme === 'light' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('edujurnal-theme', next);

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', next === 'light' ? '#f4f4f5' : '#121212');
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', next === 'light' ? 'Ganti ke tema gelap' : 'Ganti ke tema terang');
    });

    paintCharts();
}

window.paintCharts = paintCharts;

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-theme-toggle]');
    if (!button) {
        return;
    }

    const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    applyTheme(current === 'dark' ? 'light' : 'dark');
});

applyTheme(document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark');

Alpine.start();

createIcons({
    icons: {
        LayoutDashboard,
        Users,
        GraduationCap,
        School,
        BookOpen,
        CalendarDays,
        CalendarRange,
        NotebookPen,
        ClipboardCheck,
        StickyNote,
        Calculator,
        ChartColumn,
        Settings,
        CircleUser,
        LogOut,
        Menu,
        X,
        Plus,
        Search,
        Pencil,
        Trash2,
        Eye,
        Download,
        CircleCheck,
        TriangleAlert,
        Info,
        Inbox,
        Sun,
        Moon,
    },
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
        return;
    }

    const button = form.querySelector('[data-submit]');
    if (!(button instanceof HTMLButtonElement) || button.disabled) {
        return;
    }

    button.disabled = true;
    button.textContent = 'Memproses...';
});
