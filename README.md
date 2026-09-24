# eFlix - Movie Library & TV Discovery

A full-stack movie and TV show discovery application built for the eBeyond technical assessment. The frontend is a Vue 3 single-page app consuming the public TVmaze API, backed by a lightweight PHP service for contact inquiries and automated SMTP notifications.

---

## Architecture & Tech Stack

- **Frontend (`/frontend`)**: Vue 3 (Composition API, `<script setup>`), Vite, Vue Router, Vanilla CSS.
- **Backend (`/backend`)**: PHP 8+ (native, framework-free), custom socket SMTP client (`SmtpMailer`), flat-file JSON persistence.
- **External Data**: [TVmaze REST API](https://www.tvmaze.com/api) (no API key required).
- **Deployment**: Prepared for Azure Static Web Apps (frontend) and Azure App Service (backend).

---

## Project Structure

```text
├── backend/
│   ├── api/
│   │   └── contact.php         # Contact form handler & mail dispatcher
│   ├── classes/
│   │   └── SmtpMailer.php      # Custom socket-based TLS/SSL SMTP mailer
│   ├── data/
│   │   ├── submissions.json    # Flat-file store for form entries
│   │   └── emails.log          # Delivery & error log for outgoing mail
│   ├── config.php              # Central configuration loader (.env parser)
│   └── .env.example
│
├── frontend/
│   ├── public/
│   │   └── staticwebapp.config.json # Azure SPA route fallbacks
│   ├── src/
│   │   ├── components/         # Hero, Header, Footer, Contact, etc.
│   │   ├── composables/        # State management (library, favorites, form)
│   │   ├── services/           # TVmaze API client
│   │   └── views/              # MovieLibraryView, ShowDetailsView
│   ├── vite.config.js          # Dev proxy config (/api -> localhost:8000)
│   └── package.json
└── README.md
```

---

## Key Features

- **Catalogue & Discovery**: Browsing with a fixed 12-item pagination layout, switchable Grid and List views.
- **Filtering & Sorting**: Filter by genre, broadcast status, and minimum rating; sort by rating, name, or release date.
- **Remote Search**: Debounced query integration with TVmaze search endpoint.
- **Show Details View**: Dedicated route (`/show/:id`) showing banner artwork, show summary, cast gallery, and season guides.
- **Watchlist**: Client-side favorites stored in `localStorage`.
- **Contact Service**: Form validation, Google Maps office embed, and automatic dispatch of two emails:
  1. An admin notification sent to configured recipients.
  2. A branded confirmation receipt sent back to the user.

---

## API Overview

### 1. TVmaze API (External)
All entertainment content is fetched client-side from the public TVmaze REST API:

- `GET https://api.tvmaze.com/shows?page={page}`: Primary catalogue page.
- `GET https://api.tvmaze.com/search/shows?q={query}`: Show search query.
- `GET https://api.tvmaze.com/shows/{id}?embed[]=cast&embed[]=seasons`: Detailed view payload with embedded relationships.

### 2. Contact Endpoint (Internal)
- **Path**: `POST /api/contact.php`
- **Payload**:
  ```json
  {
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "telephone": "+94 77 123 4567",
    "message": "Hello, inquiring about screening times.",
    "terms": true
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Thank you! Your message has been submitted successfully.",
    "submissionId": "sub_6ab56d2185c9a6",
    "mailStatus": { "adminEmail": true, "userEmail": true }
  }
  ```

---

## Local Setup

### 1. Backend
Requires PHP 8.1+ with OpenSSL enabled (`extension=openssl` in `php.ini`):

```bash
cd backend
cp .env.example .env
# Fill in your SMTP credentials in .env
php -S 127.0.0.1:8000
```

### 2. Frontend
Requires Node.js 18+:

```bash
cd frontend
npm install
npm run dev
```

The app will be available at `http://localhost:5173`. In development, Vite proxies all `/api/*` calls directly to the PHP server running on port `8000`.

---

## Environment Variables

### Backend (`backend/.env`)
| Variable | Description | Example |
| :--- | :--- | :--- |
| `MAIL_HOST` | SMTP server host | `smtp.gmail.com` |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_USERNAME` | SMTP account email | `user@gmail.com` |
| `MAIL_PASSWORD` | SMTP password / app password | `xxxx xxxx xxxx xxxx` |
| `SMTP_ENCRYPTION` | Transport security (`tls` or `ssl`) | `tls` |
| `SMTP_FROM_NAME` | Sender display name | `eFlix Entertainment` |
| `ADMIN_EMAILS` | Comma-separated admin recipients | `admin1@example.com, admin2@example.com` |

### Frontend (`frontend/.env`)
| Variable | Description | Default |
| :--- | :--- | :--- |
| `VITE_API_BASE_URL` | Base URL of backend API in production | `""` (uses relative `/api`) |

---

## Azure Deployment Notes

- **Frontend**: Deploy `frontend/dist` to **Azure Static Web Apps**. Deep routes (`/show/:id`) are automatically rewritten to `/index.html` via `frontend/public/staticwebapp.config.json`.
- **Backend**: Deploy `backend/` to an **Azure App Service (Linux PHP 8.2+)**. Environment variables should be added under App Service **Configuration / Environment variables** instead of deploying `.env`.
