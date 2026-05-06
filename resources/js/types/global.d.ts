import { AxiosInstance } from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        axios: AxiosInstance;
        Echo: Echo;
        Pusher: typeof Pusher;
    }
}

export {};