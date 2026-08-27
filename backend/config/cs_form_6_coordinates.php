<?php

/**
 * CS Form No. 6 (Application for Leave) — field coordinates
 *
 * These were measured directly off the ORIGINAL, unedited cs-form-6.xlsx
 * (converted to PDF via LibreOffice headless, then coordinates extracted
 * from the real PDF text/vector layer — not eyeballed).
 *
 * Coordinate system: x/y in POINTS, origin at TOP-LEFT of the page
 * (y increases downward). This matches TCPDF/FPDI's default coordinate
 * system when you construct TCPDF with unit 'pt':
 *
 *   $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'pt', 'A4');
 *
 * Usage pattern for a text field:
 *   $pdf->SetXY($x, $y);
 *   $pdf->Cell(0, 0, $value);
 *
 * Usage pattern for a checkbox (draw a real ✓ checkmark only if true).
 * IMPORTANT: 'X' in the default Helvetica core font renders as a plain
 * letter X. For a proper checkmark you must switch to a Unicode font
 * that has the ✓ glyph (U+2713) before drawing it, then switch back:
 *
 *   $pdf->SetFont('dejavusans', '', 8);
 *   $pdf->SetXY($x, $y);
 *   $pdf->Cell(0, 0, "\u{2713}");
 *   $pdf->SetFont('helvetica', '', 9); // switch back for normal text
 *
 * TCPDF ships with 'dejavusans' built in — no extra font files needed.
 *
 * Page size: 595.3 x 841.9 pt (A4)
 */


return [

    // ── Section 1–5: Employee Info ──────────────────────────────
    'office_department' => ['x' => 75,  'y' => 147],
    'last_name'          => ['x' => 275, 'y' => 147],
    'first_name'         => ['x' => 360, 'y' => 147],
    'middle_name'        => ['x' => 450, 'y' => 147],
    'date_of_filing'      => ['x' => 136, 'y' => 170],
    'position'            => ['x' => 277, 'y' => 170],
    'salary'              => ['x' => 457, 'y' => 170],

    // ── Section 6.A: Type of Leave (checkboxes) ─────────────────
    'chk_vacation'            => ['x' => 70, 'y' => 227], //227
    'chk_mandatory_forced'    => ['x' => 70, 'y' => 242],
    'chk_sick'                => ['x' => 70, 'y' => 257],
    'chk_maternity'           => ['x' => 70, 'y' => 271],
    'chk_paternity'           => ['x' => 70, 'y' => 285],
    'chk_special_privilege'   => ['x' => 70, 'y' => 299],
    'chk_solo_parent'         => ['x' => 70, 'y' => 314],
    'chk_study'                => ['x' => 70, 'y' => 330],
    'chk_vawc'                 => ['x' => 70, 'y' => 344],
    'chk_rehabilitation'      => ['x' => 70, 'y' => 359],
    'chk_special_women'       => ['x' => 70, 'y' => 373],
    'chk_special_emergency'   => ['x' => 70, 'y' => 388],
    'chk_adoption'             => ['x' => 70, 'y' => 402],
    'others_specify'           => ['x' => 72, 'y' => 446],   // free-text blank

    // ── Section 6.B: Details of Leave (checkboxes + specify blanks) ──
    'chk_within_philippines'  => ['x' => 325, 'y' => 242],
    'within_philippines_text' => ['x' => 412, 'y' => 241],
    'chk_abroad'               => ['x' => 325, 'y' => 257],
    'abroad_text'              => ['x' => 394, 'y' => 255],
    'chk_in_hospital'          => ['x' => 325, 'y' => 285],
    'in_hospital_illness'      => ['x' => 428, 'y' => 285],
    'chk_out_patient'          => ['x' => 325, 'y' => 300],
    'out_patient_illness'      => ['x' => 327, 'y' => 313],
    'special_women_illness'    => ['x' => 327, 'y' => 358],
    'chk_completion_masters'  => ['x' => 325, 'y' => 388],
    'chk_bar_board_exam'      => ['x' => 325, 'y' => 403],
    'chk_monetization'         => ['x' => 325, 'y' => 432],
    'chk_terminal_leave'       => ['x' => 325, 'y' => 446],

    // ── Section 6.C / 6.D ────────────────────────────────────────
    'working_days_applied'    => ['x' => 90, 'y' => 477],
    'inclusive_dates'          => ['x' => 90, 'y' => 510],
    'chk_commutation_not_requested' => ['x' => 325,  'y' => 480],
    'chk_commutation_requested'     => ['x' => 325, 'y' => 494],
    'applicant_signature_name' => ['x' => 375, 'y' => 512],
    'applicant_signature'      => ['x' => 375, 'y' => 480],

    // ── Section 7.A: Certification of Leave Credits ─────────────
    'certification_as_of'      => ['x' => 165, 'y' => 574],
    // Small grid: columns Vacation Leave / Sick Leave
    'vl_total_earned'          => ['x' => 165, 'y' => 600],
    'sl_total_earned'          => ['x' => 240, 'y' => 600],
    'vl_less_this_application' => ['x' => 165, 'y' => 611],
    'sl_less_this_application' => ['x' => 240, 'y' => 611],
    'vl_balance'                => ['x' => 165, 'y' => 621],
    'sl_balance'                => ['x' => 240, 'y' => 621],

    // ── Section 7.B: Recommendation ─────────────────────────────
    'chk_for_approval'         => ['x' => 325, 'y' => 574],
    'chk_for_disapproval'      => ['x' => 325, 'y' => 590],
    'disapproval_reason_line'  => ['x' => 418, 'y' => 589],
    'disapproval_reason_l2'    => ['x' => 337, 'y' => 599],
    'disapproval_reason_l3'    => ['x' => 337, 'y' => 610],
    'disapproval_reason_l4'    => ['x' => 337, 'y' => 620],

    // ── Section 7.C: Approved For ────────────────────────────────
    'approved_days_with_pay'    => ['x' => 85,  'y' => 691],
    'approved_days_without_pay' => ['x' => 85,  'y' => 701],
    'approved_days_others'      => ['x' => 85,  'y' => 712],

    // ── Section 7.D: Disapproved Due To ─────────────────────────
    'disapproved_reason_l1' => ['x' => 337, 'y' => 691],
    'disapproved_reason_l2' => ['x' => 337, 'y' => 701],
    'disapproved_reason_l3' => ['x' => 337, 'y' => 711],

];
