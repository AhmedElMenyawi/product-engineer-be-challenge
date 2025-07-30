# 📌 Who, What, When - API Challenge

Welcome to the Hello Chef take-home challenge!

This project simulates a simple but powerful system used internally by our team: tracking **Who** is doing **What**, and **When**. It’s inspired by real accountability tools used by different teams at Hello Chef and we’re excited to see how you bring it to life.

---

## The Goal

Build a backend API that lets users:
- Create and assign tasks to people (`Who`)
- Describe what needs to be done (`What`)
- Define a time range (`When`)
- Mark things as complete ✅

This isn’t about scale or completeness, we want to see your **code clarity, product thinking, and ownership mindset**.

---

## Core Requirements

Your solution should demonstrate your ability to design and build a simple but usable API that supports a recurring workflow like team accountability or planning.
At a minimum, it should let users:

- Create people and assign them responsibilities (“who” and “what”)
- Set a time range for each commitment (“when”)
- Mark things as done
- View a filtered list of commitments
- Remove an entry

You’re welcome to interpret the structure and naming in a way that feels clear and maintainable to you.

---

### Bonus (Optional)
These are not required but will help showcase your product sense:
- `POST /bulk`: Accept a list of “Who, What, When” entries in JSON
- Status endpoint: Count of completed commitments by week/month
- Basic authentication or user ownership control
- A simple UI or Postman collection
- Swagger/OpenAPI docs
- Parse uploaded image to a who, what, when list

---

## Something to consider

Please include short written answers (in your README or as comments in the code):
1. **How could this be used for recurring team planning (e.g. weekly check-ins)?**
2. **If this system were used by multiple teams, how would you ensure it remains useful but not cluttered?**
3. **What signals would you look for to know if this tool is actually helping a team?**

---

## Technical Expectations

- Use Laravel (preferred), or any modern PHP framework
- Include setup instructions
- Dockerized setup is appreciated but not mandatory
- Include any test coverage or CI steps you’d normally use
- Use migrations and sensible model structure

---

## Sample Data

| Who   | What                                           | When (End)    | Done |
|-------|------------------------------------------------|---------------|------|
| Anthony  | Inform the team accountability for Product Engineer role| 2025-07-04    | ✅    |
| Raif  | Review team AI tools ("use it or lose it")     | 2025-07-25    | ❌    |
| Mohsin  | Plan use of Product Engineering gaps           | 2025-08-01  | ❌    |

---

## Submission
You can fork this public repo and send us a link to your GitHub repo (public or invite)
