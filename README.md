# ENHS-Leave-Management-System
A Leave Management System for Echague National High School developed as a Capstone Project.

## Required leave documents

Run the Laravel migrations to create document requirements and seed editable examples for the leave types that exist in the database. Administrators can manage them under **Leave Settings -> Leave Types -> Manage Documents**. Requirements can be always required, optional, or conditional on a leave-day threshold and/or filing before the leave start date. The seeded Sick Leave rule reflects the existing guidance (filed in advance or more than five days); administrators can change its conditions.

Each submitted application stores a snapshot of its applicable requirements. Uploaded files remain in the existing protected attachment storage and are available through the authenticated attachment endpoint, so later requirement edits do not change an existing application's checklist.
