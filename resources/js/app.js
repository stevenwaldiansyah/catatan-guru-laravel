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
} from 'lucide';

window.Alpine = Alpine;
window.Chart = Chart;

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
