<div align="center">

# ✈️ Rebound — Web Application & API Gateway

### Post-Booking AI Agent for Flight Crisis Handling & Schedule Changes
*Built for the **Alibaba Cloud × Atlas Agentic AI Hackathon 2026***

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38BDF8?logo=tailwind-css)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?logo=alpine.js)](https://alpinejs.dev)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

</div>

---

## 📑 Table of Contents

- [Overview](#-overview)
- [Why Rebound](#-why-rebound)
- [Tech Stack & Dependencies](#-tech-stack--dependencies)
- [Key Features & System Architecture](#-key-features--system-architecture)
- [Demo Flow](#-demo-flow)
- [Prerequisites](#-prerequisites)
- [Installation & Quickstart Guide](#-installation--quickstart-guide)
- [Demo Seed Data & Test PNRs](#-demo-seed-data--test-pnrs)
- [API Endpoints Reference](#-api-endpoints-reference)
- [Database Schema](#-database-schema)
- [AI Agent & GDS Integration](#-ai-agent--gds-integration)
- [License](#-license)

---

## 🧭 Overview

**Rebound Application** is the core web client and backend API gateway powering the Rebound Agentic AI platform. 

While conventional Online Travel Agencies (OTAs) focus on the **pre-booking** purchase funnel, Rebound operates entirely in the **post-booking** domain. It acts as an explainable recovery agent for passengers facing:
1. **Flight Disruptions** (delays, weather cancellations, gate changes).
2. **Self-Service Schedule Changes** with policy validation and automated fare rule enforcement.

The web application features a responsive 3-column dashboard UI built with Laravel Blade, Alpine.js, and Tailwind CSS, backed by a RESTful Laravel backend providing PNR authentication, chat state management, GDS simulation, and Sanctum API security.

---

## 💡 Why Rebound

When disruption happens, travellers do not need another flight-search tool—they need a clear recovery decision. Rebound combines the passenger's booking context, disruption status, ticket policy, and available alternatives to recommend the best next step and explain why it was chosen.

Rebound is designed around **human-in-the-loop travel recovery**:

1. The agent verifies the passenger's PNR and gathers the relevant booking context.
2. It explains the disruption, eligibility, waiver policy, and recommended alternatives.
3. The traveller reviews the proposed itinerary, price impact, and rationale.
4. Only after the traveller explicitly confirms does the system execute the rebooking workflow.

The current implementation uses a clearly labelled **sandbox GDS dataset** and simulated rebooking outcome. This allows the full recovery workflow to be safely demonstrated without purchasing a real ticket or performing an irreversible airline transaction. In production, the sandbox adapter can be replaced by authorised Atlas flight APIs and airline servicing integrations.

---

## 🧪 Tech Stack & Dependencies

### Backend Framework & Libraries
- **PHP**: `^8.3`
- **Laravel Framework**: `^13.17`
- **Authentication**: Laravel Sanctum (`^4.3`) & Kreait Firebase Laravel SDK (`^7.2`)
- **Database**: MySQL / SQLite (Eloquent ORM)

### Frontend Engine
- **Templating**: Laravel Blade (`resources/views`)
- **Reactivity & Client State**: Alpine.js
- **Styling**: Tailwind CSS & FontAwesome Icons
- **Dynamic In-Chat Components**: Custom Blade partials for Boarding Passes, Flight Selection Cards, QR Vouchers, and Policy Rationale Badges.

### Development Platform
- **Qoder**: Used by the team to accelerate Laravel backend implementation, including the PNR verification, persistent chat-session, and agent-workflow features.

---

## ✨ Key Features & System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      REBOUND DASHBOARD (Blade + Alpine.js)              │
│ ┌────────────────────────┐ ┌──────────────────────┐ ┌──────────────────┐ │
│ │ Left: Trip & Ticket    │ │ Center: AI Agent     │ │ Right: Flight    │ │
│ │ History                 │ │ Workspace            │ │ & Boarding Details│ │
│ └────────────────────────┘ └──────────────────────┘ └──────────────────┘ │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ REST API / Axios
┌────────────────────────────────────▼────────────────────────────────────┐
│                    LARAVEL 13 API GATEWAY & CONTROLLERS                 │
│  • AuthController: Firebase / Google OAuth + Sanctum Token Dispatch    │
│  • FlightController: PNR Verification, Activation & Mock GDS Dispatch   │
│  • ChatController: Qwen AI Integration & Session State Manager          │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ Eloquent / GDS API
┌────────────────────────────────────▼────────────────────────────────────┐
│                       DATABASE & SANDBOX GDS ADAPTER                    │
│  • User PNR Registry (user_pnrs)   • Chat Sessions & Messages          │
│  • Mock GDS Bookings               • Atlas Sandbox flight search         │
└─────────────────────────────────────────────────────────────────────────┘
```

1. **Mandatory PNR Gate**: Flight monitoring and AI interaction are locked until a valid PNR (Booking Reference) is input or scanned.
2. **Persistent AI Chat History**: Agent interactions are saved per PNR in `agent_chat_sessions` and `chat_messages` so sessions persist smoothly across refreshes.
3. **Explainable, Policy-Aware Recommendations**: The agent validates fare rules and presents the reason, waiver eligibility, and price impact behind each rebooking option.
4. **Traveller Approval Gate**: The traveller must review and explicitly confirm the selected option before the simulated rebooking workflow is completed.
5. **Sandbox Rebooking & Digital Documents**: The demo uses sandbox data to safely issue a simulated rebooking outcome, digital voucher, boarding pass, and QR/barcode.

---

## 🎬 Demo Flow

Use this single scenario to demonstrate the complete recovery workflow:

1. **Disruption detected** — Present a passenger at CGK whose `GA826` flight to SIN has been delayed because of bad weather.
2. **PNR context verified** — Rebound verifies the PNR and retrieves the passenger's booking, cabin, flight status, and applicable ticket policy from the sandbox GDS.
3. **Explainable recommendation** — Ask the agent for the best no-cost option. It explains the available alternative, disruption waiver, and fare impact.
4. **Traveller approval** — The traveller reviews the proposed `GA830` itinerary and explicitly confirms the simulated change.
5. **Recovery completed** — Show the resulting e-boarding pass, voucher, and baggage-transfer status.

> **Demo disclosure:** Rebound uses a local sandbox GDS for PNR and disruption context, and Atlas Sandbox for alternative-flight search when its credentials are configured. An internal demo-inventory fallback keeps the demonstration reliable when an Atlas sandbox route is unavailable. No live ticket is purchased, changed, or issued during this demonstration.

---

## 📋 Prerequisites

Ensure your development environment meets the following requirements before installation:

- **PHP**: `>= 8.3` (with `pdo`, `mbstring`, `openssl`, `curl`, `json` extensions)
- **Composer**: `>= 2.x`
- **Node.js**: `>= 18.x` & **NPM**: `>= 9.x`
- **Database**: MySQL `>= 8.0` or SQLite `>= 3.35`

---

## ⚡ Installation & Quickstart Guide

Follow these steps to get the application running locally:

### 1. Clone & Enter Project Directory
```bash
git clone https://github.com/tiarafthzzhr/Alibaba-Hackathon-2026.git
cd Alibaba-Hackathon-2026/rebound
```

### 2. Install PHP & Node Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
Copy the example environment file and generate the application key:
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file for database connection (e.g., MySQL or SQLite):

*For SQLite (Quick Setup):*
```env
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/rebound/database/database.sqlite
```
Create the database file if using SQLite:
```bash
touch database/database.sqlite
```

*For MySQL:*
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rebound_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Run Migrations & Seed Database
Execute the database migrations along with the Mock GDS seeder:
```bash
php artisan migrate:fresh --seed
```

### 5. Build Assets & Start Local Server
Run Vite build (or dev server) and start the Laravel development server:
```bash
# Build frontend assets
npm run build

# Start local server
php artisan serve
```

Access the application at `http://127.0.0.1:8000`.

---

## 🧪 Demo Seed Data & Test PNRs

The database seeder populates the **Mock GDS** (`mock_gds_bookings`) with pre-configured PNR codes for testing and hackathon demonstrations:

| PNR Code | Passenger Last Name | Flight No | Route | Departure | Class | Initial Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `GA826` | `ZAKARIA` | GA 826 | CGK ✈️ SIN | 2026-08-28 08:25 | Economy | ⚠️ **Delayed** |
| `SQ951` | `MAULANA` | SQ 951 | CGK ✈️ SIN | 2026-08-27 14:10 | Business | ⚠️ **Delayed** |
| `SQ638` | `ISTIQOMAH` | SQ 638 | CGK ✈️ KUL | 2026-08-29 06:40 | Economy | ✅ On Time |
| `QZ502` | `AZZAHRA` | QZ 502 | CGK ✈️ KUL | 2026-09-01 11:15 | Economy | ✅ On Time |
| `JT028` | `ZAKARIA` | JT 028 | CGK ✈️ DPS | 2026-09-03 09:50 | Economy | ❌ Cancelled |
| `GA826K`| `FIRMANSYAH` | GA 826 | CGK ✈️ SIN | 2026-08-20 08:25 | Economy | 🛈 Flown |

> 💡 **Demo Tip:** Use `PNR: GA826` and `Passenger: ZAKARIA` for the delayed-flight recovery scenario. Before recording a time-sensitive demo, set its seeded departure time to a future time. Do not use `GA826K` / `FIRMANSYAH` for rebooking: its sandbox flight status is deliberately `flown`.

---

## 🔌 API Endpoints Reference

### Public / Health
- `GET /api/health` — Checks application and database connectivity. The current GDS indicator represents the sandbox demo environment, not a live Atlas connection.

### Authentication
- `POST /api/login/google` — Authenticates Firebase Google ID Token and issues a Laravel Sanctum bearer token.

### Flight & PNR Verification (Sanctum Protected)
- `POST /api/pnr/lookup` — Queries PNR against Mock GDS & verifies passenger last name match.
- `POST /api/pnr/verify` — Validates a PNR against the sandbox GDS and records active ownership in `user_pnrs`.
- `POST /api/pnr/activate` — Sets active PNR context for the current user session.
- `GET /api/flights/alternatives?from=CGK&to=SIN&date=2026-08-31` — Searches Atlas Sandbox for alternative flights when configured, with a deterministic Rebound demo-inventory fallback for unsupported sandbox routes or failed requests.
- `POST /api/flights/rebook` — Runs the simulated rebooking workflow and returns a sandbox confirmation payload; it does not issue a real ticket.

### AI Agent Chat Engine (Sanctum Protected)
- `POST /api/chat/send` — Sends a message to the agent, stores the response, and returns dynamic UI-card payloads. Qwen reasoning is used when configured; otherwise the demo uses a deterministic simulation fallback.
- `GET /api/chat/history?pnr=GA826` — Fetches stored conversation history for the active PNR.

---

## 🗄 Database Schema

The database architecture consists of 7 core tables:

1. `users` — Store user accounts, Firebase UIDs, and avatars.
2. `user_pnrs` — Links users to verified active booking codes (`pnr_code`, `last_name`, `status`).
3. `mock_gds_bookings` — Mock GDS table seeding authoritative booking data (`pnr_code`, `last_name`, `flight_number`, `from_code`, `to_code`, `departure_time`, `cabin_class`, `status`).
4. `agent_chat_sessions` — Manages PNR-specific chat sessions.
5. `chat_messages` — Stores user/agent messages and `dynamic_ui_payload` JSON.
6. `agent_action_logs` — Audits agent tool executions (`action_type`, `payload`, `status`).
7. `compensation_vouchers` — Tracks issued crisis vouchers & QR payload keys.

---

## 🤖 AI Agent & GDS Integration

Rebound's AI agent operates via the **Reason + Act** pattern:

```
User Query ➔ Intent Classification ➔ Read Fare Rules ➔ Recommend Alternatives ➔ Traveller Approval ➔ Simulated Rebooking
```

When configured, the agent uses **Alibaba Cloud Model Studio (Qwen)** for contextual reasoning. The demo reads booking and policy context from the local sandbox GDS, queries **Atlas Sandbox** for alternative-flight search, persists its conversation and action state in Laravel, and executes only simulated rebooking outcomes. Unsupported sandbox routes safely fall back to deterministic demo inventory. Production deployment can use authorised Atlas and airline-servicing APIs while preserving the approval gate and audit trail.

---

## 📄 License

This application is open-sourced under the [MIT License](../LICENSE).

<div align="center">
  <sub>Built with ❤️ for the Alibaba Cloud × Atlas Agentic AI Hackathon 2026</sub>
</div>
