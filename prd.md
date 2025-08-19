Product Requirements Document: Breezi (MVP)
Author: Gemini AI
Date: August 17, 2025
Version: 1.1
Status: Approved for Development

1. Introduction & Vision
   Breezi is a mobile-first Progressive Web App (PWA) designed to be the all-in-one business management tool for solo AC technicians and other similar tradespeople in Trinidad and Tobago.

The vision is to empower these skilled professionals by replacing their inefficient paper-based systems with a simple, powerful digital solution that organizes their operations, streamlines invoicing, and helps them secure more recurring revenue—making the admin side of their business feel, well, breezy.

Our go-to-market strategy is a phased rollout:

Phase 1 (MVP): A stand-alone tool focused entirely on the technician.

Phase 2: A customer portal to enhance the tech-customer relationship.

Phase 3: A public marketplace to generate new leads for our technicians.

2. The Problem
   Solo AC technicians are experts at their craft but are often overwhelmed by business administration. They rely on a fragmented system of notebooks, phone calendars, and memory to manage customers, track jobs, schedule maintenance, and handle invoicing. This results in lost revenue, poor record-keeping, unprofessionalism, and significant stress.

3. Target Audience & Persona
   Our primary user is "Ron the Technician."

Profession: Self-employed AC installation and repair technician based in Trinidad and Tobago.

Technical Skills: Comfortable with his smartphone but values speed and simplicity over complex features.

Goals: Spend more time on profitable jobs, increase recurring service revenue, and appear more professional to his clients.

Frustrations: Forgetting customer details, missing service calls, and the hassle of manual invoicing.

4. MVP Features & User Stories
   The MVP is laser-focused on providing maximum value to "Ron."

Feature 1: Customer & Site Management (CRM Lite)
As Ron, I want to add and save my customers' contact information so I can find it easily.

As Ron, I want to add multiple service addresses (sites) for a single customer.

Feature 2: Asset Management
As Ron, I want to log the specific AC units (assets) at each site, including details like make, model, and installation date.

As Ron, I want to take and attach a photo of a unit's serial number for my records.

Feature 3: Job Scheduling & Management
As Ron, I want to create a new job, link it to a customer and site, and schedule it on a calendar.

As Ron, I want to receive a push notification the day before a scheduled job.

As Ron, I want to update a job's status (e.g., Scheduled, Completed).

Feature 4: Invoicing & Sales Tracking
As Ron, I want to instantly generate an invoice from a completed job.

As Ron, I want to add line items with prices to the invoice.

As Ron, I want the app to generate a clean, professional PDF of the invoice.

As Ron, I want to share that PDF with my customer via WhatsApp, email, or any app on my phone.

As Ron, I want to mark an invoice as 'Paid' and see a simple dashboard of my monthly and yearly sales.

5. Technical Architecture & Stack
   Platform: Progressive Web App (PWA), ensuring a native-like experience on mobile without the App Store.

Starter Kit: Laravel Breeze (Livewire Stack) to provide a minimal, clean foundation for authentication and boilerplate.

Core Technologies:

Backend: Laravel 11

Frontend: Livewire with Laravel Volt for rapid, single-file component development.

Templating: Blade with Tailwind CSS for a mobile-first design.

Key Packages:

spatie/laravel-pdf for server-side PDF generation.

Web Share API (Browser Native) for all sharing functionality.

Developer Admin Panel: A separate super-admin panel will be built with Filament for the app owner to manage subscribers and view application-wide analytics.

6. Design & UX Considerations
   Mobile-First: The UI must be optimized for one-handed use on a smartphone in field conditions (e.g., bright sunlight).

Speed & Simplicity: The workflow must be intuitive for a non-technical user. Every core action must be achievable in the minimum number of taps.

Branding: The design should reflect the "Breezi" name—clean, light, and stress-free.

7. Success Metrics (KPIs)
   Adoption: Number of active weekly users.

Engagement: Number of jobs and invoices created per user, per week.

Retention: Monthly churn rate of subscribers (post-launch).

Qualitative Feedback: Direct feedback from initial beta testers.

8. Out of Scope / Future Roadmap
   To maintain focus, the following are explicitly out of scope for the MVP but form our future roadmap.

Phase 2: The Customer Portal
Allow technicians to "invite" their customers to a read-only portal.

Customers can view their service history and asset information.

Customers can submit appointment requests (for tech approval).

Customers can access all their past invoices.

Phase 3: The Breezi Marketplace
A public-facing directory where homeowners can find and view profiles of Breezi technicians.

A review and rating system.

A lead-generation system for new job requests.

Future Enhancements (Backlog):
Saved line items for faster invoicing.

Simple expense tracking.

Advanced sales and service reporting.

Inventory management for parts.
