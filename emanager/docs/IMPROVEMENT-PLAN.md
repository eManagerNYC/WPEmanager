# eManager — Improvement Plan

A grounded review of the codebase with a prioritized roadmap for modules, features
and workflow. Status legend: done (✅) · in progress (🔜) · planned (⬜).

## A. Bespoke module build-out (research → workflow + fields + links + list behavior)

Bring every section to the depth of the change-order chain and the Engineering batch.

| Section | Status | Bespoke work |
|---|---|---|
| Change Management | ✅ | PCO→NOC→Directive→Proposal→COR→eTicket, role-gated, linked |
| Engineering / docs | ✅ | RFIs, Submittals, Drawings/Specs, Permitting, Meetings, Transmittals |
| Field | ✅ batch 2 | Daily Reports (submit→approve), Punch List (ball-in-court + KPIs), Checklists (workflow), Photo Library (gallery), Schedule/Linear (charts) |
| Quality & Safety | ✅ batch 3 | Inspections (→Deficiency/NCR, pass-rate KPIs), NCR (disposition + corrective/preventive + →Action Item), Deficiencies (ball-in-court), Test Records, Incidents (OSHA fields + KPIs + →Action Item), Violations & Observations (workflows) |
| Cost | ✅ batch 4 | Link commitments/invoices to budget lines (cost codes); SOV↔pay-app linkage; commitment change orders |
| Closeout | ✅ batch 5 | Commissioning checklists, punch rollover, warranty start triggers from completion certs |
| Preconstruction | ✅ batch 6 | Bid leveling comparison view; prequal scoring rollup; estimate→budget handoff |
| Resources / BIM | ✅ batch 7 | Rate-table versioning; BIM coordination issue → RFI link |

## B. Cross-cutting workflow improvements

1. ✅ **Transition data gating** — let a transition declare `requires: [fields]` (e.g. RFI
   can't go to *Answered* without an answer; COR can't be *Approved* without an amount).
   Enforce in `EM_Api::transition_record()`; surface required fields in the workflow UI.
2. ✅ **Notifications** — implement `wp_mail` on `em_record_transitioned` / assignment, with
   per-user opt-in and a digest option. (Hook exists; no sender yet — biggest feature gap.)
3. ✅ **Ball-in-court / "In my court" queue** — derive the responsible party from the workflow +
   party roles; add a global "In my court" cross-module queue on the dashboard home.
4. ⬜ **Due dates / SLA** — per-transition response-due tracking; global overdue feed.
5. ✅ **In-app workflow map** — render the state diagram on the record view (not just buttons).

## C. Module / data improvements

6. ✅ **Related Records panel** — surfaces `linked_module`/`linked_id` on the record view
   (parent + spawned children) as clickable links so the chain is navigable.
7. ✅ **Real attachments** — Media Library / upload integration to replace paste-a-URL fields.
8. ✅ **Auto-numbering** — per-module/project sequential numbers (RFI-001…) instead of manual.
9. ✅ **First-class lookups** — make `subcontractor`, `assigned_to`, `vendor` etc. lookups to
   Companies/Users instead of free text, for consistent filtering and rollups.
10. ✅ **Saved views & bulk actions** on list pages.

## D. Platform / quality

11. ✅ **Per-section permissions (by party role)** (currently capabilities are global).
12. ⬜ **Global search** across modules; **project activity feed** (activity is per-record today).
13. ⬜ **Performance** — lazy-load module definitions on navigation instead of shipping all
    100+ in the boot payload; cache `reports/stats` (one query per module today).
14. ✅ **i18n `.pot`** (`emanager/languages/emanager.pot`), **PHPUnit unit suite** (workflow,
    DB helpers, roles, all 102 module.json manifests), **JS/JSON checks**, and **GitHub Actions
    CI** (`.github/workflows/ci.yml`). See [docs/TESTING.md](../../docs/TESTING.md). _Screenshot
    PNG assets for the directory listing are still pending (owner task)._
15. ⬜ **Real Contributors / Plugin URI** before wp.org submission (placeholder today).

## Recommended sequence

**Now:** finish the section build-outs (B-batches) — Field → Quality/Safety → Cost → Closeout
→ Preconstruction → Resources/BIM. **Next:** Related Records panel (C6) + transition gating
(B1) — both small, high-value, and reusable across every module. **Then:** notifications (B2)
and attachments (C7) — the two features that most close the gap to commercial tools.
