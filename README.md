# 📌 Who, What, When – Accountability API Challenge

Welcome to the Hello Chef take-home challenge!

This project simulates a simple but powerful system used internally by our team: tracking **Who** is doing **What**, and **When**. It’s inspired by real accountability tools used by Product Engineering leads at Hello Chef — and we’re excited to see how you bring it to life.

---

## 🎯 The Goal

Build a backend API that lets users:
- Create and assign tasks to people (`Who`)
- Describe what needs to be done (`What`)
- Define a time range (`When`)
- Mark things as complete ✅

This isn’t about scale or completeness — we want to see your **code clarity, product thinking, and ownership mindset**.

---

## 🧱 Core Requirements

### 📌 Entities
You should model at least the following:
- **Person**: name, email
- **Commitment**: description (`what`), `done` status, optional tags
- **Timeframe**: start date, end date (part of a commitment)

> You may separate `Commitment` and `Event`, or keep them unified if preferred.

---

### 🧪 Suggested Endpoints

| Endpoint                | Description                                           |
|------------------------|-------------------------------------------------------|
| `POST /people`         | Create a person (`name`, `email`)                    |
| `POST /commitments`    | Create a new commitment linked to a person and dates |
| `GET /commitments`     | List commitments with filters (see below)            |
| `PATCH /commitments/:id` | Mark a commitment as done                          |
| `DELETE /commitments/:id` | Delete a commitment                               |

#### 🔍 Filters for `GET /commitments`
- By person
- By completion status
- By date range (`start_date`, `end_date`)

---

### ⭐ Bonus (Optional)
These are not required but will help showcase your product sense:
- `POST /bulk`: Accept a list of “Who, What, When” entries in JSON
- Status endpoint: Count of completed commitments by week/month
- Basic authentication or user ownership control
- A simple UI or Postman collection
- Swagger/OpenAPI docs

---

## 🧠 Product Thinking Prompts

Please include short written answers (in your README or as comments in the code):
1. **How could this be used for recurring team planning (e.g. weekly check-ins)?**
2. **What tradeoffs would you make to support 100K+ records or users?**
3. **If your users were non-technical (e.g. ops managers), how would you adapt the interface or structure?**

---

## 🧰 Technical Expectations

- Use Laravel (preferred), or any modern PHP framework
- Include setup instructions (see below)
- Dockerized setup is appreciated but not mandatory
- Include any test coverage or CI steps you’d normally use
- Use migrations and sensible model structure

---

## ✅ Sample Data (Optional)

| Who   | What                                           | When (End)    | Done |
|-------|------------------------------------------------|---------------|------|
| Ants  | Inform accountability for Product Engineer role| 2025-07-04    | ✅    |
| Ants  | Review team AI tools ("use it or lose it")     | 2025-07-25    | ❌    |
| Ants  | Plan use of Product Engineering gaps           | 2025-08-01    | ❌    |

---

## Submission
Please send us:

A link to your GitHub repo (public or invite)
