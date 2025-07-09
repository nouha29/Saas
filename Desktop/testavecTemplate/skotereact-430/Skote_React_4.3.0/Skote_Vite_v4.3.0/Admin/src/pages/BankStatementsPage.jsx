import React, { useEffect, useState } from "react";

const BankStatementsPage = () => {
  const [statements, setStatements] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStatements = async () => {
      try {
        console.log("Fetching statements...");
        const response = await fetch("/bank-statements");
        if (!response.ok) throw new Error(`Failed to fetch: ${response.status} - ${await response.text()}`);
        const data = await response.json();
        console.log("Fetched data:", data);
        setStatements(data.data || data);
      } catch (err) {
        console.error("Fetch error:", err);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchStatements();
  }, []);

  const handleInfo = async (id) => {
    try {
      const response = await fetch(`/bank-statements/${id}`, { method: "GET" });
      if (!response.ok) throw new Error("Failed to fetch details");
      const details = await response.json();
      console.log("Statement details:", details);
      alert(`Details for ${id}: ${JSON.stringify(details, null, 2)}`);
    } catch (err) {
      console.error("Info error:", err);
      alert("Could not load details.");
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm(`Delete statement ${id}?`)) {
      try {
        const response = await fetch(`/bank-statements/${id}`, { method: "DELETE" });
        if (!response.ok) throw new Error("Failed to delete");
        setStatements(statements.filter((stmt) => stmt._id !== id));
        alert("Statement deleted successfully.");
      } catch (err) {
        console.error("Delete error:", err);
        alert("Could not delete statement.");
      }
    }
  };

  if (loading) return <div className="container mt-4">Loading...</div>;
  if (error) return <div className="container mt-4 text-danger">Error: {error}</div>;

  return (
    <div className="container mt-4">
      <h1>Bank Statements</h1>
      {statements.length === 0 ? (
        <p>No processed bank statements found.</p>
      ) : (
        <ul className="list-group">
          {statements.map((stmt) => (
            <li key={stmt._id} className="list-group-item d-flex justify-content-between align-items-center">
              <span>{stmt.metadata?.filename || "Unknown"}</span>
              <div>
                <button className="btn btn-info btn-sm me-2" onClick={() => handleInfo(stmt._id)}>
                  Info
                </button>
                <button className="btn btn-danger btn-sm" onClick={() => handleDelete(stmt._id)}>
                  Delete
                </button>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default BankStatementsPage;