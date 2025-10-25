import { render } from '@wordpress/element';
import AdminApp from './components/AdminApp';
import './style.css';

document.addEventListener('DOMContentLoaded', () => {
    const appElement = document.getElementById('benribot-admin-app');
    if (appElement) {
        render(<AdminApp />, appElement);
    }
});
