import { useState } from '@wordpress/element';

export function ConnectCard({ 
    connected, 
    widgetEmbedded, 
    onConnect, 
    onAccessDashboard, 
    onToggleWidget,
    isConnecting = false,
}) {
    const [isToggling, setIsToggling] = useState(false);

    const handleToggle = async (enabled) => {
        setIsToggling(true);
        await onToggleWidget(enabled);
        setIsToggling(false);
    };

    return (
        <div className="benribot-connect-card">
            {/* Top Section: Logo and Button */}
            <div className="benribot-card-header">
                <div className="benribot-logo-section">
                    <img 
                        src={benribotAdmin.logoUrl} 
                        alt="BenriBot Logo" 
                        className="benribot-logo"
                    />
                    <span className="benribot-brand">BenriBot</span>
                </div>
                
                {connected ? (
                    <button 
                        className="benribot-btn benribot-btn-secondary"
                        onClick={onAccessDashboard}
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2L2 8L8 14M2 8H14" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        Access Dashboard
                    </button>
                ) : (
                    <button 
                        className="benribot-btn benribot-btn-primary"
                        onClick={onConnect}
                        disabled={isConnecting}
                    >
                        {isConnecting ? (
                            <svg className="benribot-loading" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 2a10 10 0 1 0 10 10" stroke="white" strokeWidth="2" strokeLinecap="round"/>
                            </svg>
                        ) : (
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 2L2 8L8 14M2 8H14" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </svg>
                        )}
                        {isConnecting ? 'Connecting…' : 'Connect Account'}
                    </button>
                )}
            </div>

            {/* Middle Section: Message and Toggle */}
            <div className="benribot-card-middle">
                <div className="benribot-message">
                    {connected ? (
                        <p>Your BenriBot AI Agent is ready to help customers find products and answer questions.</p>
                    ) : (
                        <p>Connect your store to activate your BenriBot AI Agent and start assisting customers.</p>
                    )}
                </div>
                
                {connected && (
                    <div className="benribot-toggle-section">
                        <label className="benribot-toggle-label">
                            <span className="benribot-toggle-status">
                                {widgetEmbedded ? 'Active' : 'Inactive'}
                            </span>
                            <button
                                className={`benribot-toggle ${widgetEmbedded ? 'active' : ''}`}
                                onClick={() => handleToggle(!widgetEmbedded)}
                                disabled={isToggling}
                                role="switch"
                                aria-checked={widgetEmbedded}
                            >
                                <span className="benribot-toggle-slider"></span>
                            </button>
                        </label>
                    </div>
                )}
            </div>

            {/* Bottom Section: Status */}
            <div className="benribot-card-footer">
                {connected ? (
                    <div className="benribot-status benribot-status-connected">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 4L6 11L3 8" stroke="#16a34a " strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        Account connected
                    </div>
                ) : (
                    <div className="benribot-status benribot-status-disconnected">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 3V8M8 13H8.01M15 8C15 11.866 11.866 15 8 15C4.13401 15 1 11.866 1 8C1 4.13401 4.13401 1 8 1C11.866 1 15 4.13401 15 8Z" stroke="#F59E0B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        Account not connected
                    </div>
                )}
            </div>
        </div>
    );
}
