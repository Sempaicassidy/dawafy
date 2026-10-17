/**
 * Daawafy React JS Live Terminal & POS Component
 * Embeddable React component for fast checkout & real-time stock updates.
 */

function DaawafyReactTerminal() {
    const [searchQuery, setSearchQuery] = React.useState('');
    const [drugs, setDrugs] = React.useState([]);
    const [cart, setCart] = React.useState([]);
    const [loading, setLoading] = React.useState(false);

    // Fetch drugs on query change
    React.useEffect(() => {
        setLoading(true);
        fetch(`pos_api.php?action=search&q=${encodeURIComponent(searchQuery)}`)
            .then(res => res.json())
            .then(data => {
                setDrugs(Array.isArray(data) ? data : []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [searchQuery]);

    const addToCart = (drug) => {
        setCart(prev => {
            const existing = prev.find(i => i.drug_id === drug.drug_id);
            if (existing) {
                return prev.map(i => i.drug_id === drug.drug_id ? { ...i, qty: i.qty + 1 } : i);
            }
            return [...prev, { ...drug, qty: 1 }];
        });
        if (window.notifyAddedSuccess) {
            window.notifyAddedSuccess(drug.name);
        }
    };

    const calculateTotal = () => {
        return cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.qty), 0);
    };

    return (
        <div className="row g-4">
            <div className="col-md-7">
                <div className="card shadow-sm border-0 rounded-4">
                    <div className="card-header bg-gradient text-white py-3" style={{ background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)' }}>
                        <h5 className="mb-0 fw-bold"><i className="bi bi-search me-2 text-info"></i> React JS Fast Terminal</h5>
                    </div>
                    <div className="card-body">
                        <div className="mb-3">
                            <input 
                                type="text" 
                                className="form-control form-control-lg rounded-pill px-4" 
                                placeholder="Search drug by name, generic, or sub-category..." 
                                value={searchQuery} 
                                onChange={e => setSearchQuery(e.target.value)} 
                            />
                        </div>
                        {loading ? (
                            <div className="text-center py-4"><div className="spinner-border text-primary" role="status"></div></div>
                        ) : (
                            <div className="table-responsive" style={{ maxHeight: '420px', overflowY: 'auto' }}>
                                <table className="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                            <th>Price (TZS)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {drugs.length === 0 ? (
                                            <tr><td colSpan="5" className="text-center py-4 text-muted">No medicines found.</td></tr>
                                        ) : (
                                            drugs.map(d => (
                                                <tr key={d.drug_id}>
                                                    <td>
                                                        <div className="fw-bold text-dark">{d.name}</div>
                                                        <small className="text-muted">{d.brand_name || 'Generic'}</small>
                                                    </td>
                                                    <td>
                                                        <span className="badge bg-primary mb-1 d-block">{d.category}</span>
                                                        {d.sub_category && <span className="badge bg-light text-dark border"><i class="bi bi-tag-fill me-1 text-primary"></i>{d.sub_category}</span>}
                                                    </td>
                                                    <td><span className="badge bg-success rounded-pill">{d.stock}</span></td>
                                                    <td className="fw-bold text-primary">{parseFloat(d.price).toLocaleString()}</td>
                                                    <td>
                                                        <button className="btn btn-sm btn-light-blue rounded-pill px-3" onClick={() => addToCart(d)}>
                                                            <i className="bi bi-cart-plus me-1"></i> Add
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>
            
            <div className="col-md-5">
                <div className="card shadow-sm border-0 rounded-4">
                    <div className="card-header bg-gradient text-white py-3" style={{ background: 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)' }}>
                        <h5 className="mb-0 fw-bold"><i className="bi bi-cart3 me-2"></i> React Cart ({cart.length})</h5>
                    </div>
                    <div className="card-body">
                        {cart.length === 0 ? (
                            <div className="text-center py-5 text-muted">
                                <i className="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                                Cart is empty. Select items to checkout.
                            </div>
                        ) : (
                            <div>
                                <ul className="list-group list-group-flush mb-3" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                                    {cart.map((item, idx) => (
                                        <li key={idx} className="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div>
                                                <div className="fw-bold text-dark">{item.name}</div>
                                                <small className="text-muted">TZS {parseFloat(item.price).toLocaleString()} x {item.qty}</small>
                                            </div>
                                            <span className="fw-bold text-success">TZS {(parseFloat(item.price) * item.qty).toLocaleString()}</span>
                                        </li>
                                    ))}
                                </ul>
                                <div className="border-top pt-3 mb-3">
                                    <div className="d-flex justify-content-between fs-5 fw-bold">
                                        <span>Total:</span>
                                        <span className="text-primary">TZS {calculateTotal().toLocaleString()}</span>
                                    </div>
                                </div>
                                <button className="btn btn-green btn-lg w-100 py-3 rounded-pill fw-bold" onClick={() => {
                                    if (window.notifySalesSuccess) window.notifySalesSuccess('REC-' + Math.floor(Math.random()*90000+10000), calculateTotal());
                                    if (window.notifyPaidSuccess) setTimeout(() => window.notifyPaidSuccess(calculateTotal(), 'Cash'), 500);
                                    setCart([]);
                                }}>
                                    <i className="bi bi-check-circle-fill me-2"></i> Complete Sale & Print
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

// Auto mount if container is present
document.addEventListener("DOMContentLoaded", function() {
    const mountNode = document.getElementById('daawafy-react-terminal');
    if (mountNode && window.ReactDOM) {
        const root = ReactDOM.createRoot(mountNode);
        root.render(<DaawafyReactTerminal />);
    }
});
