<?xml version="1.0" encoding="UTF-8"?>
<!--
  SCSMS – XSLT stylesheet: transport.xml → HTML dashboard report
  Modern card layout + responsive table + summary statistics
-->
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                exclude-result-prefixes="">

  <xsl:output method="html" encoding="UTF-8" indent="yes"
              doctype-system="about:legacy-compat"/>

  <xsl:template match="/transport">
    <html lang="en">
      <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>Campus Transport – Dashboard Report</title>
        <style>
          :root {
            --bg: #0f1419;
            --card: #1a2332;
            --muted: #8b9cb3;
            --text: #e8eef7;
            --accent: #3d9cf9;
            --ok: #34c759;
            --warn: #ff9f0a;
            --danger: #ff453a;
          }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: linear-gradient(160deg, #0b0f14 0%, #121a24 50%, #0f1419 100%);
            color: var(--text);
            min-height: 100vh;
          }
          .wrap { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
          header {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 1rem; margin-bottom: 1.5rem;
          }
          h1 { font-size: 1.35rem; margin: 0; letter-spacing: 0.02em; }
          .badge {
            background: rgba(61, 156, 249, 0.15);
            color: var(--accent);
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            border: 1px solid rgba(61, 156, 249, 0.35);
          }
          .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
          }
          .card {
            background: var(--card);
            border-radius: 12px;
            padding: 1rem 1.1rem;
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
          }
          .card h2 {
            margin: 0 0 0.35rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            font-weight: 600;
          }
          .card .num { font-size: 1.75rem; font-weight: 700; }
          .card.available .num { color: var(--ok); }
          .card.full .num { color: var(--danger); }
          .card.total .num { color: var(--accent); }
          .table-wrap {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            overflow: auto;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
          }
          table { width: 100%; border-collapse: collapse; min-width: 640px; }
          th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); }
          th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            background: rgba(0,0,0,0.2);
          }
          tr:hover td { background: rgba(61, 156, 249, 0.06); }
          .pill {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
          }
          .st-Available { background: rgba(52, 199, 89, 0.2); color: #8ef0a8; }
          .st-Full { background: rgba(255, 69, 58, 0.2); color: #ffb4ae; }
          .st-Maintenance { background: rgba(255, 159, 10, 0.2); color: #ffd28a; }
          footer { margin-top: 1.25rem; font-size: 0.8rem; color: var(--muted); text-align: center; }
        </style>
      </head>
      <body>
        <div class="wrap">
          <header>
            <div>
              <h1>Smart Campus Services – Transport Dashboard</h1>
              <p style="margin:0.35rem 0 0;color:var(--muted);font-size:0.9rem;">
                Generated from XML via XSLT (Group 5 – Campus Transport System)
              </p>
            </div>
            <span class="badge">XSLT Report</span>
          </header>

          <!-- Summary cards using XSLT count() -->
          <section class="cards">
            <div class="card total">
              <h2>Total trips</h2>
              <div class="num"><xsl:value-of select="count(trip)"/></div>
            </div>
            <div class="card available">
              <h2>Available trips</h2>
              <div class="num"><xsl:value-of select="count(trip[status='Available'])"/></div>
            </div>
            <div class="card full">
              <h2>Full trips</h2>
              <div class="num"><xsl:value-of select="count(trip[status='Full'])"/></div>
            </div>
            <div class="card">
              <h2>Maintenance</h2>
              <div class="num"><xsl:value-of select="count(trip[status='Maintenance'])"/></div>
            </div>
          </section>

          <section class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Route</th>
                  <th>Driver</th>
                  <th>Departure</th>
                  <th>Arrival</th>
                  <th>Capacity</th>
                  <th>Available</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <xsl:for-each select="trip">
                  <xsl:sort select="route"/>
                  <tr>
                    <td><xsl:value-of select="position()"/></td>
                    <td><xsl:value-of select="route"/></td>
                    <td><xsl:value-of select="driver"/></td>
                    <td><xsl:value-of select="schedule/departure"/></td>
                    <td><xsl:value-of select="schedule/arrival"/></td>
                    <td><xsl:value-of select="capacity"/></td>
                    <td><xsl:value-of select="availableSeats"/></td>
                    <td>
                      <span>
                        <xsl:attribute name="class">
                          <xsl:text>pill st-</xsl:text>
                          <xsl:value-of select="status"/>
                        </xsl:attribute>
                        <xsl:value-of select="status"/>
                      </span>
                    </td>
                  </tr>
                </xsl:for-each>
              </tbody>
            </table>
          </section>

          <footer>
            SCSMS Transport Module · XSLT 1.0 · Report snapshot
          </footer>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
