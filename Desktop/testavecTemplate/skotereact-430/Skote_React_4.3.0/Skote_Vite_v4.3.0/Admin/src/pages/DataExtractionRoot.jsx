import React from "react";
import { Route, Routes, Navigate, useNavigate, Link } from "react-router-dom";
import BankStatementsPage from "./BankStatementsPage";
import { useTranslation } from "react-i18next"; // Assuming i18n is set up
import ExtractionDashboard from "./ExtractionDashboard";

const DataExtractionRoot = () => {
  const navigate = useNavigate();
  const { t } = useTranslation();

  return (
    <div className="container-fluid mt-4">
      <div className="row">
        {/* Sidebar */}
        <nav className="col-md-2 d-md-block bg-light sidebar">
          <div className="position-sticky">
            <ul className="nav flex-column">
              <li className="nav-item">
                <Link to="/data-extraction" className="nav-link has-arrow">
                  <i className="bx bx-data"></i>
                  <span>{t("Data Extraction")}</span>
                </Link>
                <ul className="sub-menu" aria-expanded="false">
                  <li>
                    <Link to="/data-extraction/bank-statements" className="nav-link">
                      {t("View Processed Bank Statements")}
                    </Link>
                  </li>
                  <li>
                    <Link to="/data-extraction" className="nav-link">
                      {t("Extract My Data")}
                    </Link>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>

        {/* Main Content */}
        <main className="col-md-9 ms-sm-auto col-lg-10 px-md-4">
          <Routes>
            <Route path="/data-extraction" element={<ExtractionDashboard/>} />
            <Route path="/data-extraction/bank-statements" element={<BankStatementsPage />} />
            <Route path="/" element={<Navigate to="/data-extraction" replace />} />
            <Route path="*" element={<Navigate to="/data-extraction" replace />} />
          </Routes>
        </main>
      </div>
    </div>
  );
};

export default DataExtractionRoot;