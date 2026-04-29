declare module 'vue-router' {
    interface RouteParams {
        [key: string]: string | number | undefined;
    }
}

// Fix for route() function
declare function route(name: string, params?: Record<string, string | number>): string;