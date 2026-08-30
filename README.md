<div align="center">

# Rebound

### AI Agent for Flight Crisis Handling & Schedule Changes (Post-Booking)



[![Hackathon](https://img.shields.io/badge/Alibaba%20Cloud%20x%20Atlas-Agentic%20AI%20Hackathon-orange)]()
[![Platform](https://img.shields.io/badge/Built%20with-Qoder-blue)]()
[![Cloud](https://img.shields.io/badge/Powered%20by-Alibaba%20Cloud-FF6A00)]()
[![API](https://img.shields.io/badge/Data-Atlas%20Travel%20API-1f6feb)]()
[![License](https://img.shields.io/badge/License-MIT-green)]()

</div>

---

> 💡 **How to read this document**
> Every technical section opens with a plain-language explanation marked **🟢 For Everyone**,
> followed by the details marked **🔵 For Technical Readers**.
> That way this document is comfortable to read for non-technical people and developers alike.

---

## 📑 Table of Contents

**Concept**
- [Overview](#-overview)
- [A Simple Analogy](#-a-simple-analogy)
- [Problem Background](#-problem-background)
- [What Makes Rebound Different](#-what-makes-rebound-different)
- [Key Features](#-key-features)

**How It Works**
- [How the Agent Works](#️-how-the-agent-works)
- [Data Sources & the Role of the PNR](#-data-sources--the-role-of-the-pnr)
- [Sample Atlas API Response](#sample-atlas-api-response)
- [Authorization Model](#-authorization-model)
- [Agent Tools](#-agent-tools)
- [Known Design Gap — Multi-Segment Itineraries](#️-known-design-gap--multi-segment-itineraries)
- [User Flows](#-user-flows)

**Technical**
- [Architecture](#-architecture)
  - [System Overview (As Built)](#system-overview-as-built)
  - [Agent Request Flow](#agent-request-flow-post-apichatsend)
  - [Design Decisions Worth Noting](#design-decisions-worth-noting)
  - [Implementation Status](#implementation-status)
- [Data Model](#-data-model)
- [Tech Stack](#-tech-stack)
- [Target Architecture (Post-Hackathon)](#-target-architecture-post-hackathon)
- [Sandbox Environment](#-sandbox-environment)
- [Quick Start & Setup Guide](#-quick-start--setup-guide)

**Impact & Judging**
- [Alignment with the UN SDGs](#-alignment-with-the-un-sdgs)
- [Judging Criteria Mapping](#️-judging-criteria-mapping)
- [Roadmap](#-roadmap)
- [License](#-license)

---

# 🧩 Concept

## 🧭 Overview

**Rebound** is an **agentic AI application** that solves the two most crucial problems in air travel — the ones that have been most neglected:

1. **Flight disruptions** (delays / cancellations)
2. **Self-service schedule changes** with ticket policy validation

Unlike Online Travel Agents (OTAs) such as Traveloka or tiket.com, Rebound is **not** a place to buy new tickets. Rebound works in the **post-booking** phase: the user already holds a ticket, and Rebound's AI agent handles what happens *next* when plans change.

---

## 🍰 A Simple Analogy

Imagine you have already bought a ticket, and then something goes wrong (your flight is delayed) or you want to change your schedule.

- **The old way:** you have to call a call center, wait a long time, read confusing ticket rules, and find a solution on your own.
- **With Rebound:** you simply chat with an AI assistant — just like a normal conversation. The AI reads the rules, calculates the fees, searches for replacement flights, and handles the ticket reissue. **All you do is approve.**

> Rebound is like a **personal flight assistant** that is always on standby: it moves first when something goes wrong, and takes care of the complicated parts so you don't have to.

---

## 🎯 Problem Background

**🟢 For Everyone:** When a flight goes wrong, passengers are often confused and stressed. They have to handle everything themselves, even though the rules are complicated.

Post-booking problems that still aren't handled well:

- ⏳ Waiting for hours to reach a call center when a delay happens.
- 📜 **Fare rules** are complicated, opaque, and rarely explained in plain language.
- 😰 During a crisis, passengers are left to work out compensation and rebooking on their own.
- 🔁 Changing a schedule often involves confusing policies and hidden fees.

> OTAs are excellent at **selling** tickets, but offer little intelligent support **after** the purchase. **Rebound fills this gap.**

---

## 🆚 What Makes Rebound Different

Rebound deliberately strips out everything that doesn't serve its two core business processes:

| Traditional OTA (Traveloka / tiket.com) | Rebound (by design) |
| :--- | :--- |
| Search & buy new tickets | ❌ — the user already has a ticket |
| Hotel, train, and attraction catalogs | ❌ — focused on one problem |
| Many payment methods & checkout flows | ❌ — a single confirm button |
| Dashboards, tabs, complex navigation | ❌ — a single conversation screen |
| Users find solutions themselves | ✅ — **the AI acts proactively** |

> **Positioning:** *A Post-Booking Crisis & Change Handling Agent — not just another ticket booking app.*

---

## ✨ Key Features

- 🤖 **Proactive Crisis Handling** — the AI reacts to problems *before* the user asks for help.
- 📜 **Policy-Aware Reasoning** — the AI **always reads the fare rules first** before offering any change, then explains them in plain language.
- 💬 **Chat-Based Interface** — one conversation, no complicated menus.
- 🎨 **Dynamic UI Inside the Chat** — the AI renders flight cards, QR vouchers, and boarding passes directly in the chat, not just plain text.
- ⚡ **Minimal Interaction** — a crisis is resolved in ~2 taps; a schedule change in ~3 taps.
- 🔍 **Transparent & Auditable** — every AI decision comes with a clear policy rationale.

---

# ⚙️ How It Works

## ⚙️ How the Agent Works

**🟢 For Everyone:**
Rebound's AI works like a capable assistant. It **thinks → picks an action → performs it → checks the result → thinks again**, repeating until your problem is genuinely solved. It doesn't just guess an answer; it actually checks the data and the rules before acting.

**🔵 For Technical Readers:**
Rebound uses an **agentic loop (ReAct)** pattern. The LLM reasons, selects a *tool*, calls the Atlas API, observes the result, then reasons again until the goal is reached.

```
User / Signal  →  AI reasons  →  select an Action (Tool)  →  call the Atlas API
               →  observe the result  →  reason again  →  render result / ask  →  repeat
```

**Key rule — Policy-Aware Guardrail:**
The AI is **forbidden** from offering a replacement flight before validating the fare rules. This is what makes Rebound more than a mere "search bot" — it is a **decision-making agent** that genuinely understands policy.

---

## 🔑 Data Sources & the Role of the PNR

**🟢 For Everyone:**
An important question: *Is Rebound connected to the platform where the ticket was bought (e.g. Traveloka, Trip.com, etc.)?* **No.**

The actual ticket data is not stored at the platform you bought from — that platform is only the point of purchase (the storefront). The real data lives in the **airline's system**. What connects you to it is the **booking code / PNR** (a 6-character code such as `ABC123`) that you receive after buying the ticket.

> **Analogy:** a PNR is like a **bank account number**. Your money isn't kept inside the "number" — it's kept at the **bank**. The account number is just the *key* to access it. The same goes for a PNR: it's the key that unlocks your real ticket data.

**🔵 For Technical Readers:**
Rebound is **OTA-agnostic**. The authoritative booking data lives in the **airline system / GDS (Global Distribution System)** — such as Amadeus, Sabre, or Travelport. Rebound accesses it through the **Atlas Travel API**, using the **PNR** as the identifying key.

```
Passenger  →  Rebound (AI Agent)  →  Atlas Travel API  →  Airline System / GDS
                                                          (authoritative data source)
```

**A real example — Garuda on Nov 30 ➜ moving it up to Nov 26:**

```
Step 1  The passenger provides the PNR:  "Booking code ABC123, please move it up to Nov 26"
              │
Step 2  get_flight_status("ABC123")
        → Fetch the real data: "Garuda GA-xxx, Nov 30, class Y"
              │
Step 3  read_fare_rules("Y")           ← REQUIRED first (Policy-Aware)
        → "Class Y tickets are changeable, an admin fee applies"
              │
Step 4  search_alternatives("CGK", "KUL", "Nov 26", "Y")
        → Look for available seats on Nov 26
              │
Step 5  Render the Nov 26 flight card + fare difference + admin fee
              │
Step 6  The passenger taps "Confirm" → reissue_ticket() → the Nov 26 ticket is issued
```

### Sample Atlas API Response

**🟢 For Everyone:** Below is real output we retrieved from the Atlas Travel API — the actual list of flights the agent has to work with in Step 4 above. We include it as evidence that the Atlas integration has been verified against live sandbox data, not just designed on paper.

**🔵 For Technical Readers:** `search_alternatives("CGK", "KUL", ...)` — direct flights, sandbox environment.

**Direct Flights**

| Departure | Arrival | Flight | Duration | Price (USD) |
| :--- | :--- | :--- | :--- | :--- |
| 05:00 CGK | 08:10 KUL | ID8397 (Batik Air) | 2h 10m | **$72.34 — cheapest** |
| 08:35 CGK | 11:35 KUL | AK381 (AirAsia) | 2h 0m | $116.48 |
| 09:00 CGK | 12:15 KUL | OD399 (Batik Air Malaysia) | 2h 15m | $92.75 |
| 11:00 CGK | 14:10 KUL | 8B673 (TransNusa) | 2h 10m | $95.89 |
| 13:10 CGK | 16:15 KUL | AK352 (AirAsia) | 2h 5m | $116.48 |
| 14:15 CGK | 17:30 KUL | OD389 (Batik Air Malaysia) | 2h 15m | $104.12 |
| 16:45 CGK | 19:45 SZB* | 8B699 (TransNusa) | 2h 0m | $94.76 |

\* SZB = Sultan Abdul Aziz Shah Airport (Subang), Kuala Lumpur area.

> ⚠️ **Note:** these are sample figures captured from the Atlas **sandbox** at a point in time — they are illustrative, not live fares.

**What this tells the agent design:**

- **Multiple carriers per route** — the agent must rank options, not just take the first result. Cheapest (`$72.34`) and fastest (`2h 0m`) are different flights, so "best" needs an explicit rule.
- **Alternate airports in the same city** — the last row lands at **SZB (Subang)** rather than KUL. The agent must surface this clearly, since a passenger expecting KUL would otherwise be moved to a different airport without noticing.

---

> **The advantage:** because all it needs is a PNR, Rebound can serve tickets from **any OTA** without a separate integration per platform.

---

## 🔐 Authorization Model

**🟢 For Everyone:**
Rebound cannot change someone's ticket without permission. That is why Rebound operates on the basis of **user consent** — just like a financial app that may access your account only after you grant permission. The user grants authority, and Rebound then acts **on the user's behalf**.

**🔵 For Technical Readers:**
Rebound implements a *consent-first* **Trust & Authorization Layer**:

- **User consent** → the user authorizes Rebound to act on their behalf.
- **Official channels** → for OTA-managed tickets, integration happens through official partnerships/APIs.
- **Audit trail** → every agent action is logged and traceable.

> Aligned with one of the hackathon tracks: **"trust and verification systems"**.

---

## 🧰 Agent Tools

**🟢 For Everyone:** This is the list of "capabilities" the AI has — the concrete things it can actually do.

**🔵 For Technical Readers:** The agent's capabilities are exposed as a clearly defined *tool registry*:

| Tool | What it does | Used in |
| :--- | :--- | :--- |
| `get_flight_status(pnr)` | Fetches flight status & delay predictions | Flow 1 |
| `read_fare_rules(ticket_code)` | Reads the fare rules → changeable? at what cost? | Flows 1 & 2 |
| `search_alternatives(from, to, date, cabin_class)` | Searches for replacement flights | Flows 1 & 2 |
| `check_compensation(delay_minutes)` | Calculates compensation entitlements (voucher / snack) | Flow 1 |
| `hold_seat(flight_id)` | Temporarily locks a seat | Flows 1 & 2 |
| `reissue_ticket(pnr, new_flight)` | Reissues and updates the ticket | Flows 1 & 2 |

### ⚠️ Known Design Gap — Multi-Segment Itineraries

**🟢 For Everyone:** Long-haul trips are rarely direct. A flight to Istanbul might go Jakarta → Dubai → Istanbul. If the first leg is delayed, the passenger misses the connection — and fixing *one* flight is not enough, because the rest of the journey falls apart with it.

**🔵 For Technical Readers:** Two of our tools currently assume a single-leg trip:

- `search_alternatives(from, to, date, cabin_class)` treats the journey as one origin–destination pair.
- `reissue_ticket(pnr, new_flight)` accepts a single replacement flight, so it cannot express a re-planned multi-leg itinerary.

The Atlas API does return connecting itineraries, so the data supports this — our tool signatures do not yet. Handling it properly means re-planning the whole chain (including alternative connection points) and reissuing every affected segment as one atomic operation, while still validating fare rules for each.

We are documenting this openly rather than hiding it: it is the most significant limitation in the current design, and the clearest next step after the core tools are live.

---

## 🔄 User Flows

Rebound has two main flows. Both are made as simple as possible.

### Flow 1 — Crisis Handling (the AI Moves First)

**🟢 In short:** the flight is delayed → the AI immediately notifies you and offers solutions (a voucher or a reschedule) → all you do is choose.

```
┌────────────────────────────────────────────────────────────────┐
│  1. The system detects that your flight is delayed              │
│     (simulated via a "Trigger" button during the demo)          │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  2. You receive a notification:                                 │
│     "Your flight is weather-affected. Tap to see options."      │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  3. The app opens → the AI greets you & explains the situation  │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
                 ┌── AI assesses the delay length ──┐
                 ▼                                  ▼
      ┌─────────────────────┐        ┌──────────────────────────┐
      │ MINOR DELAY ≤ 2 hrs  │        │  MAJOR DELAY > 4 hrs      │
      │ → Voucher eligible   │        │  → Rescheduling advised   │
      └──────────┬──────────┘        └─────────────┬────────────┘
                 ▼                                  ▼
      ┌─────────────────────┐        ┌──────────────────────────┐
      │ Voucher card shown   │        │  2–3 replacement flights  │
      │ → "Claim" button     │        │  shown (Covered by the    │
      │                      │        │  Airline)                 │
      └──────────┬──────────┘        └─────────────┬────────────┘
                 ▼                                  ▼
      ┌─────────────────────┐        ┌──────────────────────────┐
      │ A QR code appears    │        │  You pick one → the AI    │
      │ (redeem at airport)  │        │  issues the new ticket →  │
      │ → DONE ✅             │        │  boarding pass → DONE ✅   │
      └─────────────────────┘        └──────────────────────────┘
```

### Flow 2 — Self-Service Schedule Change (the AI Reads the Rules for You)

**🟢 In short:** you type "change my schedule" → the AI reads the fare rules → it shows the options and costs transparently → you confirm → the new ticket is issued.

```
┌────────────────────────────────────────────────────────────────┐
│  1. You open the app & type your request in plain language:     │
│     "Change my Kuala Lumpur flight to tomorrow morning."        │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  2. The AI shows its process transparently:                     │
│     "Reading your ticket policy..."                             │
│     "Searching for available schedules..."                      │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  3. The AI reads the fare rules FIRST  ← the core selling point │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  4. The AI shows a flight card + the Policy Rationale:          │
│     "Class Y ticket — change permitted, admin fee applies."     │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  5. You tap "Continue" → the AI shows the fare difference       │
└───────────────────────────┬────────────────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│  6. You tap "Confirm" → the new ticket is issued in the chat ✅  │
│     (no payment method to pick — just one button)               │
└────────────────────────────────────────────────────────────────┘
```

---

# 🏗 Technical

## 🏗 Architecture

**🟢 For Everyone:** Rebound's system is split into layers that work together — from the interface you see, to the AI brain, to the bridge to the data, to the flight data source itself.

This section describes **what is actually built and running in this repository today**. Where a piece is still simulated or not yet wired up, we say so explicitly rather than describing the finished vision as if it already existed. The intended production shape is documented separately in [Target Architecture](#-target-architecture-post-hackathon).

**🔵 For Technical Readers:** Rebound is currently a **single Laravel 13 monolith** (`rebound/`) that serves both the web UI and the JSON API. There is no separate agent service and no serverless deployment yet — the agent loop, the API gateway, and the GDS stand-in all run inside the same PHP application.

### System Overview (As Built)

```
┌──────────────────────────────────────────────────────────────────────┐
│  1. PRESENTATION LAYER — Blade + Alpine.js (server-rendered)          │
│     resources/views/**                                                │
│     • layouts/app.blade.php   — shell, Tailwind CDN, Alpine, JsBarcode│
│     • welcome.blade.php       — single-screen dashboard               │
│     • sections/*.blade.php    — the dynamic chat components:          │
│         chat-area · flight-recommendation · ticket-policy ·           │
│         boarding-pass · qr-modal · pnr-onboarding-modal · …           │
│     • auth/login · auth/register — Firebase JS SDK obtains an ID token│
│                                                                       │
│     State lives in Alpine; components are toggled by flags the        │
│     backend returns (showTicketPolicy / showRecommendation).          │
└───────────────────────────┬──────────────────────────────────────────┘
                            │  fetch() → JSON  (Sanctum / session auth)
┌───────────────────────────▼──────────────────────────────────────────┐
│  2. HTTP LAYER — routes/web.php + routes/api.php                      │
│     web.php  → guest routes, /auth/firebase, /lang/{locale},          │
│                the auth-protected dashboard, /logout                  │
│     api.php  → auth:sanctum group:                                    │
│                POST /api/pnr/lookup      POST /api/pnr/verify         │
│                POST /api/pnr/activate    POST /api/chat/send          │
│                GET  /api/chat/history    GET  /api/flights/alternatives│
│                POST /api/flights/rebook                               │
│                GET  /api/health (public)                              │
└───────────────────────────┬──────────────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────────────┐
│  3. APPLICATION LAYER — app/Http/Controllers                          │
│                                                                       │
│   AuthController    Firebase ID token → verifyIdToken() (Admin SDK)   │
│                     → User::firstOrCreate(firebase_uid)               │
│                     → web: Laravel session │ api: Sanctum token       │
│                                                                       │
│   FlightController  PNR ownership & authorization                     │
│                     lookup() / verify()  → check Mock GDS, match      │
│                                             passenger surname         │
│                     activate()           → persist active PNR         │
│                     Invariant: one 'active' PNR per user; the         │
│                     previous one is demoted to 'changed'.             │
│                                                                       │
│   ChatController    The agent turn (see Agent Request Flow below)     │
│                     sendMessage() · history()                         │
│                     getSystemPrompt() — strict JSON-only contract     │
│                     callQwenLLM()    — HTTP → Qwen, else fallback     │
│                     simulateAgenticAI() — keyword reasoning engine    │
└───────────────────────────┬──────────────────────────────────────────┘
                            │  Eloquent
┌───────────────────────────▼──────────────────────────────────────────┐
│  4. DATA LAYER — MySQL (rebound_db)                                   │
│     users · user_pnrs · agent_chat_sessions · chat_messages ·         │
│     agent_action_logs · compensation_vouchers · mock_gds_bookings     │
│     personal_access_tokens (Sanctum)                                  │
└───────────────────────────┬──────────────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────────────┐
│  5. EXTERNAL SERVICES                                                 │
│     ✅ Firebase Auth (kreait/laravel-firebase) — live, verifies tokens│
│     🟡 Qwen / DashScope — code path complete, activates when an       │
│        API key is present; otherwise the simulation engine answers    │
│     🟡 Mock GDS — the mock_gds_bookings table stands in for the       │
│        Atlas API / real GDS; swapping it out is the next milestone    │
└──────────────────────────────────────────────────────────────────────┘
```

### Agent Request Flow (`POST /api/chat/send`)

This is the core path of the whole product — what happens between the user pressing Enter and a card appearing in the chat. Implemented in [`ChatController::sendMessage()`](rebound/app/Http/Controllers/ChatController.php).

```
 1. Validate                 message + pnr are required
 2. Authorize the PNR        UserPnr::where(user_id, pnr_code) — a user may only
                             ever talk about a PNR bound to their own account.
                             Not theirs → 403, no LLM call is made.
 3. Resolve the session      AgentChatSession::firstOrCreate(user_id, pnr_code)
 4. Persist the user turn    ChatMessage(sender: 'user')
 5. Build flight_context     from mock_gds_bookings: flight number, route,
                             departure, cabin class, status, waiver eligibility
 6. Build chat_history       the last 6 messages, oldest-first → LLM memory
 7. Reason                   callQwenLLM(message, flight_context, chat_history)
                               ├─ API key set → POST to Qwen (temp 0.3,
                               │                response_format: json_object)
                               │                → parseStrictJson() strips any
                               │                  ``` fences and validates
                               └─ no key / error / unparseable
                                                → simulateAgenticAI() fallback
 8. Persist the agent turn   ChatMessage(sender: 'agent') + dynamic_ui_payload
 9. Respond                  the JSON contract below → Alpine renders the card
```

**The UI contract.** Every agent turn — whether it came from the real LLM or the fallback — returns the same shape, which is what lets the frontend render a card instead of a wall of text:

```json
{
  "type": "text | policy_card | options_list | disruption_alert | success_card",
  "replyId": "Indonesian response",
  "replyEn": "English response",
  "showTicketPolicy": false,
  "showRecommendation": false
}
```

`type` selects the component; the two booleans toggle the policy and recommendation panels. Because the same contract is enforced on both paths, **the demo works identically with or without an LLM key** — the fallback degrades the reasoning, never the interface.

### Design Decisions Worth Noting

| Decision | Rationale |
| :--- | :--- |
| **Graceful LLM degradation** | `callQwenLLM()` falls back to `simulateAgenticAI()` on a missing key, an HTTP error, a timeout, or unparseable output. A live demo can never hard-fail on a network hiccup. |
| **Bilingual at the data layer** | Both `replyId` and `replyEn` are generated in one pass and stored together, so the ID/EN switch is instant and never re-queries the model. |
| **PNR ownership as a guardrail** | Authorization is checked in step 2, *before* any model call. The agent structurally cannot reason about a booking the user doesn't own. |
| **Strict JSON, defensively parsed** | The system prompt forbids Markdown, and `parseStrictJson()` still strips ``` fences and validates — prompt instructions are treated as a request, not a guarantee. |
| **Mock GDS behind a real seam** | `MockGdsBooking` is queried only inside `FlightController` and `ChatController`; replacing it with the Atlas API is a localized change, not a rewrite. |
| **Server-rendered, CDN assets** | Blade + Alpine over a CDN keeps the whole app one `php artisan serve` away — no build step is required to run the demo. |

### Implementation Status

**🟢 For Everyone:** An honest scoreboard of which parts are genuinely working versus still simulated.

| Component | Status | Notes |
| :--- | :---: | :--- |
| Auth (Google + email/password) | ✅ Live | Firebase Admin SDK verifies every ID token server-side |
| Database schema & migrations | ✅ Live | All 7 domain tables migrated and seeded |
| Chat UI + dynamic components | ✅ Live | Cards, QR, boarding pass with scannable Code128 barcodes |
| PNR verification & ownership | ✅ Live | Against the Mock GDS, with surname matching |
| Conversation persistence | ✅ Live | Survives refresh via `GET /api/chat/history` |
| Qwen LLM integration | 🟡 Ready | Code path complete; **activates once `QWEN_API_KEY` is set** — see [LLM Configuration](#llm-configuration) |
| Agentic reasoning | 🟡 Simulated | Keyword-driven `simulateAgenticAI()`; a true ReAct tool loop is the next milestone |
| Atlas Travel API | 🟡 Mocked | `mock_gds_bookings` + static demo endpoints stand in for live GDS calls |
| Formal tool registry | ⬜ Planned | The six tools are specified but not yet registered as callable JSON schemas |
| `agent_action_logs` writes | ⬜ Planned | Table exists; population lands with the tool loop |
| Alibaba Function Compute | ⬜ Planned | Currently all logic runs inside the Laravel monolith |

#### LLM Configuration

The Qwen client reads its settings from `config('services.qwen.*')`, falling back to environment variables. To activate the real LLM, add to `.env`:

```env
QWEN_API_KEY=your_dashscope_api_key
QWEN_API_ENDPOINT=https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions
QWEN_MODEL_NAME=qwen-max
```

> ⚠️ **Note:** `config/services.php` does not yet define a `qwen` block, so the `config()` lookup currently resolves to `null` and the code falls through to `env()`. Because `php artisan config:cache` disables `env()` at runtime, add the block below to `config/services.php` before deploying with a cached config:
>
> ```php
> 'qwen' => [
>     'api_key'  => env('QWEN_API_KEY'),
>     'endpoint' => env('QWEN_API_ENDPOINT'),
>     'model'    => env('QWEN_MODEL_NAME', 'qwen-max'),
> ],
> ```

---

## 🗄 Data Model

**🟢 For Everyone:** How the information is organized — who the user is, which tickets are theirs, what was said in the chat, and what the AI did.

**🔵 For Technical Readers:** Seven domain tables. `pnr_code` is the string that ties a conversation to a booking.

```
users ──1:N──> user_pnrs             a user's verified, owned bookings
  │                                  unique(user_id, pnr_code)
  │                                  status: active | changed | cancelled | flown
  │
  ├──1:N──> agent_chat_sessions ──1:N──> chat_messages
  │            (user_id, pnr_code,          (session_id, sender, message_content,
  │             context_summary)              dynamic_ui_payload JSON, sent_at)
  │                    │                     sender: user | agent | system
  │                    │
  │                    └──1:N──> agent_action_logs
  │                                (tool_name, tool_arguments JSON,
  │                                 policy_rationale JSON, status)
  │                                 ← the audit trail for every agent action
  │
  └──1:N──> compensation_vouchers
               (pnr_code, qr_code_string UNIQUE, voucher_type, is_redeemed)

mock_gds_bookings   standalone — the airline's side of the world
  (pnr_code, last_name, flight_number, from_code, to_code,
   departure_time, cabin_class, status)
```

Two details worth calling out:

- **`chat_messages.dynamic_ui_payload`** is the JSON column that makes the chat replayable. It stores the card type and panel flags alongside the text, so `GET /api/chat/history` can reconstruct the rendered conversation — cards included — rather than replaying it as flat text.
- **`agent_action_logs.policy_rationale`** exists to satisfy the *auditable agent* requirement: every tool call is meant to record not just what the agent did, but the fare-rule reasoning that justified it. The table is migrated; writes land with the tool loop.

---

## 🧪 Tech Stack

**Currently in the repository:**

| Category | Technology |
| :--- | :--- |
| **Framework** | Laravel 13 (PHP 8.3+; developed on 8.4) |
| **Frontend** | Blade server-rendering + Alpine.js 3 (CDN) |
| **Styling** | Tailwind CSS (Play CDN at runtime; Tailwind 4 + Vite available for builds) |
| **Auth** | Firebase Authentication via `kreait/laravel-firebase` 7 |
| **API auth** | Laravel Sanctum 4 (token) + session guard (web) |
| **Database** | MySQL (`rebound_db`) |
| **LLM client** | Qwen / DashScope OpenAI-compatible endpoint (`Http::post`) |
| **Build tooling** | Vite 8 + `laravel-vite-plugin` |
| **Extras** | JsBarcode (IATA Code128 boarding passes), bilingual ID/EN via `lang/` |

**Target platform (hackathon submission):**

| Category | Technology |
| :--- | :--- |
| **Agent Platform** | Qoder |
| **LLM** | Qwen (Alibaba Cloud Model Studio) |
| **Compute** | Alibaba Cloud Function Compute |
| **Database** | Alibaba Cloud RDS / Redis |
| **Travel API** | Atlas Travel API |

---

## 🎯 Target Architecture (Post-Hackathon)

**🟢 For Everyone:** This is where the system is heading — the same product, but with each layer running as its own cloud service instead of one application.

**🔵 For Technical Readers:** The as-built monolith maps cleanly onto the four-layer target. The seams already exist; the work is extraction, not redesign.

```
┌─────────────────────────────────────────────────────────────┐
│  1. PRESENTATION LAYER                                       │
│     Chat UI + dynamic component renderer + push notifications│
│     ← today: Blade + Alpine (already in place)               │
└───────────────────────────┬─────────────────────────────────┘
                            │  responses + rendering
┌───────────────────────────▼─────────────────────────────────┐
│  2. AGENT LAYER (AI Brain)   ── built on QODER ──            │
│     • LLM: Qwen (Alibaba Cloud Model Studio)                 │
│     • Orchestrator (ReAct reasoning loop)                    │
│     • Tool Registry (the agent's capability list)            │
│     • Policy-Aware Guardrail (fare rules must be validated)  │
│     ← today: ChatController — single-turn, no tool loop yet  │
└───────────────────────────┬─────────────────────────────────┘
                            │  tool calls
┌───────────────────────────▼─────────────────────────────────┐
│  3. INTEGRATION LAYER (Bridge)                               │
│     Alibaba Cloud Function Compute                           │
│     Tool wrappers → Atlas API + disruption signal receiver   │
│     ← today: FlightController + static demo endpoints        │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│  4. DATA & EXTERNAL SERVICES                                 │
│     • Atlas Travel API → bridge to Airline Systems / GDS     │
│     • Database (user PNRs, audit logs) — Alibaba RDS/Redis   │
│     ← today: mock_gds_bookings + local MySQL                 │
└─────────────────────────────────────────────────────────────┘
```

**Technology mapping:**

| Layer | Technology | Role |
| :--- | :--- | :--- |
| AI Brain | **Qoder** | Builds & runs the core agentic reasoning |
| LLM & Compute | **Alibaba Cloud** | Qwen (Model Studio) + Function Compute + Database |
| Actions & Data | **Atlas Travel API** | Bridge to airline/GDS systems for all real operations |

**The migration path, in order:**

1. Register the six tools as JSON schemas and replace the single-turn call with a real ReAct loop — the biggest functional gap.
2. Swap `MockGdsBooking` queries for Atlas API calls behind the same method signatures.
3. Start writing `agent_action_logs` on every tool invocation, completing the audit trail.
4. Extract the tool wrappers into Function Compute; Laravel stays as the presentation and auth tier.
5. Point the database at Alibaba RDS, with Redis for sessions and cache.

---

## 🧭 Sandbox Environment

**🟢 For Everyone:**
During the competition, Rebound does not touch real airline systems. Instead, the organizers provide a **sandbox environment** — a "practice room" filled with **simulated data** that is safe to test against.

It already contains **booking records (PNRs) seeded as the initial state**. When a user requests a schedule change, the agent **does not create a new booking** — it **updates the existing record**. Successfully changing that data is the **proof** that the agent works correctly.

**🔵 For Technical Readers:**
- Test data in the form of PNRs is *seeded* as the initial state in the Atlas API sandbox.
- The `reissue_ticket()` operation performs an *update* on an existing record, not a *create*.
- Comparing the before → after state (e.g. Nov 30 → Nov 26) serves as end-to-end functional validation.

> ℹ️ The disruption (delay) signal is also **simulated**, via a single trigger ("Trigger Storm") during the demo. All the logic behind it remains fully functional and representative of production conditions.

---

## 🚀 Quick Start & Setup Guide

To run the **Rebound** web application and API gateway locally:

```bash
# 1. Navigate to the application folder
cd rebound

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure the database in .env, then migrate & seed
#    The project is developed against MySQL:
#      DB_CONNECTION=mysql
#      DB_DATABASE=rebound_db
#    Create the schema first, then:
php artisan migrate:fresh --seed

# 5. Start the dev server
php artisan serve          # → http://127.0.0.1:8000
```

**Required configuration:**

| Variable | Needed for | If omitted |
| :--- | :--- | :--- |
| `DB_*` | All persistence | The app cannot boot |
| `FIREBASE_CREDENTIALS` | Server-side token verification | Login fails |
| `FIREBASE_WEB_*` | The browser Firebase SDK | The login page cannot obtain a token |
| `QWEN_API_KEY` | Live LLM reasoning | Falls back to the simulation engine — the demo still runs |

Verify the stack is up with the public health endpoint:

```bash
curl http://127.0.0.1:8000/api/health
```

> ℹ️ Assets are loaded from CDNs at runtime (Tailwind Play, Alpine, JsBarcode), so **no build step is required** to run the demo. `npm install && npm run build` is only needed if you switch to the Vite-compiled pipeline.

For endpoint details, the database schema, and demo seed data, see [Architecture](#-architecture) and [Data Model](#-data-model) above.

---

# 🌍 Impact & Judging

## 🌍 Alignment with the UN SDGs

| SDG | Relevance | Priority |
| :--- | :--- | :---: |
| **SDG 9 — Industry, Innovation & Infrastructure** | Builds modern agentic AI infrastructure that innovates the travel industry. | ⭐ Primary |
| **SDG 12 — Responsible Consumption & Production** | Efficient rescheduling reduces empty seats & wasted flights, lowering the carbon footprint. | Supporting |
| **SDG 8 — Decent Work & Economic Growth** | Automates repetitive support tasks, freeing human agents for complex cases. | Supporting |

---

## ⚖️ Judging Criteria Mapping

| Criterion | Weight | How Rebound Meets It |
| :--- | :---: | :--- |
| **Innovation** | 30% | Policy-Aware Reasoning, proactive AI, and dynamic UI inside the chat |
| **Feasibility** | 30% | A narrow post-booking scope, six well-defined tools, a concrete architecture, sandbox-ready |
| **Use of Qoder** | 20% | The agent's reasoning core is built & run on Qoder |
| **Impact & Presentation** | 20% | Real-world crisis storytelling + clear SDG alignment |

---

## 🗺 Roadmap

**Done**

- [x] Build the chat-based frontend with dynamic components
- [x] Verify the Atlas Travel API returns usable flight data ([sample response](#sample-atlas-api-response))
- [x] Authentication (email/password + Google Sign-In via Firebase)
- [x] Database schema for PNRs, chat sessions, messages, action logs, and vouchers


**Next phase — beyond the hackathon scope**

- [ ] **Multi-segment rebooking** — when one leg is disrupted and a connection becomes unreachable, re-plan and reissue the *entire* itinerary rather than a single flight. See [Known Design Gap](#-known-design-gap--multi-segment-itineraries).
- [ ] **Alternate-airport awareness** — surface replacement flights that land at a different airport serving the same city (e.g. SZB instead of KUL), so a passenger is never silently rerouted.
- [ ] **Pre-booking trip planning** — deliberately out of scope for now. Searching and buying new tickets is OTA territory, and Rebound's value comes from staying focused on the post-booking phase.



## 📄 License

This project is released under the **MIT License**. See [`LICENSE`](LICENSE) for details.

---

<div align="center">

*Built for the Alibaba Cloud × Atlas Agentic AI Hackathon.*

**Rebound — when plans change, let the agent handle the rest.**
#hackhaton #alibaba
</div>
