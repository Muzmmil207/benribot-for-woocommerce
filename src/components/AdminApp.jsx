import { useState, useEffect } from '@wordpress/element';
import { ConnectCard } from './ConnectCard';

export default function AdminApp() {
    const [status, setStatus] = useState({
        connected: false,
        widgetEmbedded: false,
        loading: true
    });
    const [isConnecting, setIsConnecting] = useState(false);

    // Fetch connection status on mount
    useEffect(() => {
        fetchConnectionStatus();
    }, []);

    const fetchConnectionStatus = async () => {
        try {
            const response = await fetch('/wp-json/benribot/v1/status', {
                headers: {
                    'X-WP-Nonce': benribotAdmin.apiNonce
                }
            });
            const data = await response.json();
            setStatus({
                connected: data.connected || false,
                widgetEmbedded: data.widget_embedded || false,
                loading: false
            });
        } catch (error) {
            console.error('Error fetching status:', error);
            setStatus(prev => ({ ...prev, loading: false }));
        }
    };

    const handleConnect = async () => {
        if (isConnecting) return;
        setIsConnecting(true);
        try {
            const response = await fetch('/wp-json/benribot/v1/connect', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': benribotAdmin.apiNonce
                }
            });
            const data = await response.json();
            
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }
            alert('Unexpected response. Please try again.');
        } catch (error) {
            console.error('Error connecting:', error);
            alert('Failed to initiate connection. Please try again.');
        } finally {
            // In case we didn't navigate away
            setIsConnecting(false);
        }
    };

    const handleAccessDashboard = () => {
        window.open('https://app.benribot.com/dashboard', '_blank');
    };

    const handleToggleWidget = async (enabled) => {
        try {
            const response = await fetch('/wp-json/benribot/v1/widget-toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': benribotAdmin.apiNonce
                },
                body: JSON.stringify({ enabled })
            });
            const data = await response.json();
            
            if (data.success) {
                setStatus(prev => ({ ...prev, widgetEmbedded: enabled }));
            }
        } catch (error) {
            console.error('Error toggling widget:', error);
        }
    };

    if (status.loading) {
        return (
            <div className="benribot-admin-loading">
                <p>Loading...</p>
            </div>
        );
    }

    return (
        <ConnectCard
            connected={status.connected}
            widgetEmbedded={status.widgetEmbedded}
            onConnect={handleConnect}
            onAccessDashboard={handleAccessDashboard}
            onToggleWidget={handleToggleWidget}
            isConnecting={isConnecting}
        />
    );
}
