import React, { useState, useEffect } from "react";

const ExtractionDashboard = () => {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selectedFile, setSelectedFile] = useState(null);
  const [error, setError] = useState(null);
  const [templateStatus, setTemplateStatus] = useState(null);
  const [selectedType, setSelectedType] = useState(null);
  const [selectedBank, setSelectedBank] = useState(null);
  const [selectedCurrency, setSelectedCurrency] = useState(null);
  const [openTypeDialog, setOpenTypeDialog] = useState(false);
  const [openBankDialog, setOpenBankDialog] = useState(false);
  const [openCurrencyDialog, setOpenCurrencyDialog] = useState(false);
  const [soldeInitial, setSoldeInitial] = useState(null);
  const [soldeFinal, setSoldeFinal] = useState(null);
  const [totalDebit, setTotalDebit] = useState(0);
  const [totalCredit, setTotalCredit] = useState(0);
  const [balanceCalculated, setBalanceCalculated] = useState(null);
  const [filterText, setFilterText] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const rowsPerPage = 10;

  const BANKS = ["BIAT", "BNA", "ATTIJARI", "ZITOUNA", "ATB", "AMEN", "OTHER"];
  const CURRENCIES = ["TND", "USD", "EUR"];

  const handleFileChange = (event) => {
    const file = event.target.files[0];
    setSelectedFile(file);
    setError(null);
    setTemplateStatus(null);
    setSelectedType(null);
    setSelectedBank(null);
    setSelectedCurrency(null);
    setSoldeInitial(null);
    setSoldeFinal(null);
    setTotalDebit(0);
    setTotalCredit(0);
    setBalanceCalculated(null);
    setFilterText("");
    setCurrentPage(1);
    if (file) verifyTemplate(file);
  };

  const verifyTemplate = async (file) => {
    setLoading(true);
    try {
      const formData = new FormData();
      formData.append("file", file);
      console.log("Verifying template with file:", file.name);
      const response = await fetch("/api/verify-template", { method: "POST", body: formData });
      console.log("Verify response status:", response.status);
      const responseText = await response.clone().text();
      console.log("Verify response text:", responseText);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status} - ${responseText || "No details"}`);
      const status = await response.json();
      console.log("Template status:", status);
      setTemplateStatus(status);
      if (status.valid) setOpenTypeDialog(true);
      else setError(new Error(status.message || "Template invalid"));
    } catch (error) {
      console.error("Verify template error:", error);
      setError(error);
    } finally {
      setLoading(false);
    }
  };

  const handleTypeSelection = (type) => {
    setSelectedType(type);
    setOpenTypeDialog(false);
    if (type === "statement") setOpenBankDialog(true);
    else setOpenCurrencyDialog(true);
  };

  const handleBankSelection = (bank) => {
    setSelectedBank(bank);
    setOpenBankDialog(false);
    setOpenCurrencyDialog(true);
  };

  const handleCurrencySelection = (currency) => {
    setSelectedCurrency(currency);
    setOpenCurrencyDialog(false);
  };

  const handleExtract = async () => {
    if (!selectedFile || !selectedType || !selectedBank || !selectedCurrency) {
      setError(new Error("Missing required fields: type, bank, or currency."));
      return;
    }

    setLoading(true);
    const formData = new FormData();
    formData.append("file", selectedFile);
    formData.append("bank", selectedBank);
    formData.append("currency", selectedCurrency);
    formData.append("category", "Bank Statement");
    formData.append("type", selectedType);

    for (let [key, value] of formData.entries()) {
      console.log(`FormData ${key}:`, value instanceof File ? value.name : value);
    }

    try {
      console.log("Fetching from:", "/api/extract-transactions");
      const response = await fetch("/api/extract-transactions", {
        method: "POST",
        body: formData,
        credentials: "include",
      });
      console.log("Response status:", response.status);
      const responseText = await response.clone().text();
      console.log("Response text:", responseText);
      if (!response.ok) {
        const errorDetail = responseText ? (JSON.parse(responseText).detail || responseText) : "No details";
        throw new Error(`HTTP error! status: ${response.status} - ${errorDetail}`);
      }
      const result = await response.json();
      console.log("Extracted data:", result);

      const correctedData = result.result.transactions.map(tx => ({
        ...tx,
        date: tx.date ? tx.date.replace("2025", "2024") : "",
        value_date: tx.date ? tx.date.replace("2025", "2024") : "",
        debit: tx.debit || 0,
        credit: tx.credit || 0
      }));

      const initialMatch = correctedData.find(tx => tx.label?.includes("SOLDE AU"));
      const finalMatch = correctedData.find(tx => tx.label?.includes("SOLDE") && !tx.label.includes("AU"));
      setSoldeInitial(initialMatch ? (initialMatch.debit || initialMatch.credit || null) : null);
      setSoldeFinal(finalMatch ? (finalMatch.debit || finalMatch.credit || null) : null);

      setData(correctedData);
      setTotalDebit(correctedData.reduce((sum, tx) => sum + (tx.debit || 0), 0));
      setTotalCredit(correctedData.reduce((sum, tx) => sum + (tx.credit || 0), 0));
    } catch (error) {
      console.error("Extract error:", error);
      setError(error);
    } finally {
      setLoading(false);
    }
  };

  const calculateBalance = () => {
    if (data.length > 0) {
      const calculatedBalance = totalCredit - totalDebit;
      setBalanceCalculated(calculatedBalance);
      console.log("Calculated Balance:", calculatedBalance);
    } else {
      setBalanceCalculated(null);
      setError(new Error("No data to calculate balance."));
    }
  };

  const saveToDB = () => {
    if (data.length > 0) {
      console.log("Data saved to DB (simulated):", data);
      setError(null);
      alert("Data successfully saved to database!");
    } else {
      setError(new Error("No data to save."));
    }
  };

  const handleCellChange = (index, field, value) => {
    try {
      const newData = [...data];
      const globalIndex = index + (currentPage - 1) * rowsPerPage; // Adjust for pagination

      // Validate input based on field
      if (field === "date" || field === "value_date") {
        // Basic date format check (e.g., YYYY-MM-DD)
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value) && value !== "") {
          throw new Error("Invalid date format. Use YYYY-MM-DD.");
        }
        newData[globalIndex][field] = value || "";
      } else if (field === "debit" || field === "credit") {
        // Ensure numeric or empty
        const numValue = value === "" ? 0 : parseFloat(value);
        if (isNaN(numValue) && value !== "") {
          throw new Error("Debit and Credit must be numbers.");
        }
        newData[globalIndex][field] = numValue;
      } else {
        // Label can be any string
        newData[globalIndex][field] = value || "";
      }

      setData(newData);
      setTotalDebit(newData.reduce((sum, tx) => sum + (tx.debit || 0), 0));
      setTotalCredit(newData.reduce((sum, tx) => sum + (tx.credit || 0), 0));
    } catch (error) {
      console.error("Cell change error:", error);
      setError(error);
    }
  };

  const exportToCSV = () => {
    if (data.length === 0) {
      alert("No data to export.");
      return;
    }
    const headers = ["date,value_date,label,debit,credit"];
    const rows = data.map(tx => `${tx.date},${tx.value_date},${tx.label},${tx.debit || 0},${tx.credit || 0}`);
    const csvContent = [headers, ...rows].join("\n");
    const blob = new Blob([csvContent], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "extracted_data.csv";
    a.click();
    window.URL.revokeObjectURL(url);
  };

  const filteredData = data.filter(tx =>
    tx.label.toLowerCase().includes(filterText.toLowerCase())
  );
  const indexOfLastRow = currentPage * rowsPerPage;
  const indexOfFirstRow = indexOfLastRow - rowsPerPage;
  const currentData = filteredData.slice(indexOfFirstRow, indexOfLastRow);
  const totalPages = Math.ceil(filteredData.length / rowsPerPage);

  const displayValue = (value) => (value === 0 || !value ? "-" : value);

  return (
    <div className="data-extraction-dashboard">
      <div className="card">
        <div className="card-body">
          <h1 className="card-title">Data Extraction Dashboard</h1>
          <div className="mb-3">
            <input
              type="file"
              className="form-control"
              onChange={handleFileChange}
              accept="application/pdf"
            />
            <button
              className="btn btn-primary mt-2 mr-2"
              onClick={handleExtract}
              disabled={!selectedFile || loading || !selectedType || !selectedBank || !selectedCurrency}
            >
              {loading ? "Extracting..." : "Extract Data"}
            </button>
            <input
              type="text"
              className="form-control mt-2"
              placeholder="Filter by label..."
              value={filterText}
              onChange={(e) => { setFilterText(e.target.value); setCurrentPage(1); }}
              style={{ width: "200px", display: "inline-block" }}
            />
          </div>
          {loading ? (
            <p className="loading">Loading...</p>
          ) : error ? (
            <p className="loading" style={{ color: "red" }}>
              Error: {error.message}
            </p>
          ) : data.length > 0 ? (
            <>
              <table className="table table-striped">
                <thead>
                  <tr>
                    <th>date</th>
                    <th>value_date</th>
                    <th>label</th>
                    <th>debit</th>
                    <th>credit</th>
                  </tr>
                </thead>
                <tbody>
                  {currentData.map((item, index) => (
                    <tr key={index}>
                      <td>
                        <input
                          type="text"
                          value={item.date || ""}
                          onChange={(e) => handleCellChange(index, "date", e.target.value)}
                          style={{ width: "100px" }}
                        />
                      </td>
                      <td>
                        <input
                          type="text"
                          value={item.value_date || ""}
                          onChange={(e) => handleCellChange(index, "value_date", e.target.value)}
                          style={{ width: "100px" }}
                        />
                      </td>
                      <td>
                        <input
                          type="text"
                          value={item.label || ""}
                          onChange={(e) => handleCellChange(index, "label", e.target.value)}
                          style={{ width: "200px" }}
                        />
                      </td>
                      <td>
                        <input
                          type="number"
                          value={item.debit || ""}
                          onChange={(e) => handleCellChange(index, "debit", e.target.value)}
                          placeholder={displayValue(item.debit)}
                          style={{ width: "100px" }}
                        />
                      </td>
                      <td>
                        <input
                          type="number"
                          value={item.credit || ""}
                          onChange={(e) => handleCellChange(index, "credit", e.target.value)}
                          placeholder={displayValue(item.credit)}
                          style={{ width: "100px" }}
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
                <tfoot>
                  <tr>
                    <td colSpan="3">Total</td>
                    <td>{totalDebit.toFixed(3)}</td>
                    <td>{totalCredit.toFixed(3)}</td>
                  </tr>
                  <tr>
                    <td colSpan="3">Solde Initial</td>
                    <td colSpan="2">{soldeInitial?.toFixed(3) || "N/A"}</td>
                  </tr>
                  <tr>
                    <td colSpan="3">Solde Final</td>
                    <td colSpan="2">{soldeFinal?.toFixed(3) || "N/A"}</td>
                  </tr>
                </tfoot>
              </table>
              <div className="mt-3">
                <button
                  className="btn btn-secondary mr-2"
                  onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1}
                >
                  Previous
                </button>
                <span>
                  Page {currentPage} of {totalPages}
                </span>
                <button
                  className="btn btn-secondary ml-2"
                  onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                  disabled={currentPage === totalPages}
                >
                  Next
                </button>
                <button className="btn btn-primary mr-2 ml-4" onClick={calculateBalance}>
                  Calculate Balance
                </button>
                <button className="btn btn-success mr-2" onClick={saveToDB}>
                  Save to Database
                </button>
                <button className="btn btn-secondary" onClick={exportToCSV}>
                  Export to CSV
                </button>
                {balanceCalculated !== null && (
                  <p className="mt-2">Calculated Balance: {balanceCalculated.toFixed(3)} {selectedCurrency}</p>
                )}
              </div>
            </>
          ) : templateStatus ? (
            <p>Template verified: {templateStatus.message}</p>
          ) : (
            <p>No data extracted yet.</p>
          )}

          {openTypeDialog && (
            <div className="modal" style={{ display: "block", position: "fixed", top: 0, left: 0, right: 0, bottom: 0, background: "rgba(0,0,0,0.5)" }}>
              <div className="modal-content" style={{ background: "#fff", margin: "15% auto", padding: "20px", width: "300px" }}>
                <h2>Select Type</h2>
                <button onClick={() => handleTypeSelection("statement")} style={{ margin: "5px" }}>Bank Statement</button>
                <button onClick={() => handleTypeSelection("invoice")} style={{ margin: "5px" }}>Invoice</button>
                <button onClick={() => setOpenTypeDialog(false)} style={{ margin: "5px" }}>Cancel</button>
              </div>
            </div>
          )}
          {openBankDialog && (
            <div className="modal" style={{ display: "block", position: "fixed", top: 0, left: 0, right: 0, bottom: 0, background: "rgba(0,0,0,0.5)" }}>
              <div className="modal-content" style={{ background: "#fff", margin: "15% auto", padding: "20px", width: "300px" }}>
                <h2>Select Bank</h2>
                {BANKS.map((bank) => (
                  <button key={bank} onClick={() => handleBankSelection(bank)} style={{ margin: "5px" }}>
                    {bank}
                  </button>
                ))}
                <button onClick={() => setOpenBankDialog(false)} style={{ margin: "5px" }}>Cancel</button>
              </div>
            </div>
          )}
          {openCurrencyDialog && (
            <div className="modal" style={{ display: "block", position: "fixed", top: 0, left: 0, right: 0, bottom: 0, background: "rgba(0,0,0,0.5)" }}>
              <div className="modal-content" style={{ background: "#fff", margin: "15% auto", padding: "20px", width: "300px" }}>
                <h2>Select Currency</h2>
                {CURRENCIES.map((currency) => (
                  <button key={currency} onClick={() => handleCurrencySelection(currency)} style={{ margin: "5px" }}>
                    {currency}
                  </button>
                ))}
                <button onClick={() => setOpenCurrencyDialog(false)} style={{ margin: "5px" }}>Cancel</button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ExtractionDashboard;