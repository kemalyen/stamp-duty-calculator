# Plan: Stamp Duty Calculator

## Goal

Build a standalone Stamp Duty Land Tax (SDLT) calculator for residential property purchases in England. The calculator sits on our quote flow between the client entering their property details and seeing the full quote. For this task, build a standalone version — it does not need to integrate with anything else.

## Scope

**In scope:** current standard residential SDLT for England, first-time buyer relief, and the additional property surcharge. A working browser-based UI a real client could use, and automated tests covering the calculation logic.

**Out of scope:** non-residential and mixed-use rates, linked transactions, Multiple Dwellings Relief, Welsh LTT, Scottish LBTT, the non-resident surcharge, leasehold rent calculations, historical rates, persistence, and authentication.

## What the user does and sees

The user enters their purchase details and sees the SDLT they would owe, broken down so they can follow where the number comes from, along with the effective rate as a percentage of the price.

What "purchase details" means in practice is partly a judgement call. The plan deliberately doesn't list the exact input fields — work out what the calculator needs to know to produce a correct answer for the three rate scenarios below, and design the inputs accordingly. If you find yourself wanting to ask us a clarifying question about what to collect from the user, that's the call we want you to make and note.

The breakdown should make sense to someone who has never heard the phrase "nil rate band." Plain language beats jargon.

## Architecture

A dedicated Laravel service performs the calculation. It takes the user's inputs and returns a structured result containing the total, the breakdown, and the effective rate. The service must be pure — same inputs, same outputs, no database queries, no side effects, no external calls.

The SDLT rate bands, thresholds, and surcharge percentages must live in **configuration**, not in the calculation logic. When HMRC changes rates, the fix should be a config edit and a test update, not a code change. How you structure that config — Laravel config file, JSON, YAML, or something else — is your call. The requirement is separation of data from logic.

Three rate scenarios need to be supported: standard rates, first-time buyer relief, and the additional property surcharge. Standard and first-time buyer rates are progressive band structures; the surcharge is a flat addition that applies on top. Research the current rules from HMRC's published guidance before you start — the details of how each scenario behaves at the edges matter, and getting them right is part of what's being assessed.

## Validation and UX

Invalid input — non-numeric, empty, negative, nonsensical combinations — should produce clear messages, never a server error or a blank page. Loading, error, and result states should all be handled even if the interface is plain. Think about what happens when a user changes inputs after seeing a result.

A plain interface that handles every case well scores higher than a polished one that crashes on edge cases.

## Testing

Write tests that prove the maths is correct. At minimum cover the three rate scenarios (standard, first-time buyer, additional property) and at least one band-boundary case where the price falls exactly on a threshold. Add whatever else you think matters — edge cases around zero or very low prices, the interaction between first-time buyer relief and the price cap, the surcharge's effect on lower bands. Use your judgement on what's worth testing and what isn't.

A mix of unit tests on the calculation service and feature tests on the HTTP endpoint is ideal.

**Verify your test expectations against HMRC's own calculator before submitting.** If your numbers and HMRC's disagree, one of you has the wrong rates — and it's almost certainly not HMRC.

## Judgement calls

A few things in this plan are deliberately left for you to decide:

- Exactly what inputs to collect from the user
- How the rate config is structured
- How the breakdown is displayed
- Whether calculation happens via form POST, AJAX, or client-side (server-side preferred but not required)
- Whether to work in pence or pounds internally
- How you handle combinations of inputs that don't make sense together

Where you hit a judgement call, make the call and note it briefly — in a code comment or in PROCESS.md. We're more interested in how you handle ambiguity than in whether you pick the same answer we would.