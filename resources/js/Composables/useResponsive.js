/**
 * Responsive Composable
 * Provides reactive breakpoint detection and responsive utilities
 */

import { ref, onMounted, onUnmounted, computed } from 'vue';

export function useResponsive() {
    // Breakpoint values (matching Tailwind config)
    const breakpoints = {
        xs: 475,
        sm: 640,
        md: 768,
        lg: 1024,
        xl: 1280,
        '2xl': 1536,
        '3xl': 1920,
    };

    // Current window width
    const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

    // Reactive breakpoint states
    const isMobile = computed(() => windowWidth.value < breakpoints.md);
    const isTablet = computed(() => windowWidth.value >= breakpoints.md && windowWidth.value < breakpoints.lg);
    const isDesktop = computed(() => windowWidth.value >= breakpoints.lg);
    const isWide = computed(() => windowWidth.value >= breakpoints['2xl']);

    // Specific breakpoint checks
    const isXs = computed(() => windowWidth.value >= breakpoints.xs);
    const isSm = computed(() => windowWidth.value >= breakpoints.sm);
    const isMd = computed(() => windowWidth.value >= breakpoints.md);
    const isLg = computed(() => windowWidth.value >= breakpoints.lg);
    const isXl = computed(() => windowWidth.value >= breakpoints.xl);
    const is2xl = computed(() => windowWidth.value >= breakpoints['2xl']);
    const is3xl = computed(() => windowWidth.value >= breakpoints['3xl']);

    // Current breakpoint name
    const currentBreakpoint = computed(() => {
        if (windowWidth.value < breakpoints.xs) return 'base';
        if (windowWidth.value < breakpoints.sm) return 'xs';
        if (windowWidth.value < breakpoints.md) return 'sm';
        if (windowWidth.value < breakpoints.lg) return 'md';
        if (windowWidth.value < breakpoints.xl) return 'lg';
        if (windowWidth.value < breakpoints['2xl']) return 'xl';
        if (windowWidth.value < breakpoints['3xl']) return '2xl';
        return '3xl';
    });

    // Device orientation
    const isPortrait = ref(typeof window !== 'undefined' ? window.innerHeight > window.innerWidth : true);
    const isLandscape = computed(() => !isPortrait.value);

    // Touch device detection
    const isTouchDevice = ref(
        typeof window !== 'undefined' &&
        ('ontouchstart' in window || navigator.maxTouchPoints > 0)
    );

    // Update function
    const updateDimensions = () => {
        if (typeof window !== 'undefined') {
            windowWidth.value = window.innerWidth;
            isPortrait.value = window.innerHeight > window.innerWidth;
        }
    };

    // Debounced resize handler
    let resizeTimeout;
    const handleResize = () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateDimensions, 150);
    };

    // Lifecycle hooks
    onMounted(() => {
        if (typeof window !== 'undefined') {
            window.addEventListener('resize', handleResize);
            updateDimensions();
        }
    });

    onUnmounted(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', handleResize);
            clearTimeout(resizeTimeout);
        }
    });

    return {
        // Window dimensions
        windowWidth,

        // Device categories
        isMobile,
        isTablet,
        isDesktop,
        isWide,

        // Specific breakpoints
        isXs,
        isSm,
        isMd,
        isLg,
        isXl,
        is2xl,
        is3xl,

        // Current breakpoint
        currentBreakpoint,

        // Orientation
        isPortrait,
        isLandscape,

        // Touch detection
        isTouchDevice,

        // Breakpoint values
        breakpoints,
    };
}
