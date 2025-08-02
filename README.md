Design and Architectural Decisions

This section outlines the key architectural and design choices made during the development of the API

Interpreting "People" as Authenticated Users

A key decision was how to interpret the "Who" in the "Who, What, When" model. The requirement "Create people and assign them responsibilities" could be seen as either creating simple data records or creating full user accounts.
I have chosen to implement "People" as authenticated Users of the system.

Reasoning:
For an internal accountability tool to be effective, it requires a concept of ownership and context. An authenticated system allows a user to log in and see commitments assigned to them or that they have created, which is how a real team would use this tool.

This approach is a prerequisite for meaningful features like user ownership, filtering ("show me my tasks"), and potential future enhancements like notifications or role-based permissions. It also directly addresses the optional bonus point for "Basic authentication or user ownership control."
