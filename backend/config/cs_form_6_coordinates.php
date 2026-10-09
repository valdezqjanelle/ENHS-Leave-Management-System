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
    'office_department' => ['x' => 76,  'y' => 140],
    'last_name'          => ['x' => 285, 'y' => 140],
    'first_name'         => ['x' => 370, 'y' => 140],
    'middle_name'        => ['x' => 460, 'y' => 140],
    'date_of_filing'      => ['x' => 137, 'y' => 161],
    'position'            => ['x' => 277, 'y' => 161],
    'salary'              => ['x' => 456, 'y' => 159],

    // ── Section 6.A: Type of Leave (checkboxes) ─────────────────
    'chk_vacation'            => ['x' => 71, 'y' => 217],
    'chk_mandatory_forced'    => ['x' => 71, 'y' => 232],
    'chk_sick'                => ['x' => 71, 'y' => 247],
    'chk_maternity'           => ['x' => 71, 'y' => 261],
    'chk_paternity'           => ['x' => 71, 'y' => 275],
    'chk_special_privilege'   => ['x' => 71, 'y' => 290],
    'chk_solo_parent'         => ['x' => 71, 'y' => 305],
    'chk_study'                => ['x' => 71, 'y' => 320],
    'chk_vawc'                 => ['x' => 71, 'y' => 334],
    'chk_rehabilitation'      => ['x' => 71, 'y' => 349],
    'chk_special_women'       => ['x' => 71, 'y' => 363],
    'chk_special_emergency'   => ['x' => 71, 'y' => 378],
    'chk_adoption'             => ['x' => 71, 'y' => 392],
    'others_specify'           => ['x' => 72, 'y' => 433],   // free-text blank

    // ── Section 6.B: Details of Leave (checkboxes + specify blanks) ──
    'chk_within_philippines'  => ['x' => 323, 'y' => 232],
        'within_philippines_text' => ['x' => 410, 'y' => 231],
    'chk_abroad'               => ['x' => 323, 'y' => 247],
        'abroad_text'              => ['x' => 400, 'y' => 245],
    'chk_in_hospital'          => ['x' => 323, 'y' => 275],
        'in_hospital_illness'      => ['x' => 427, 'y' => 275],
    'chk_out_patient'          => ['x' => 323, 'y' => 290],
        'out_patient_illness'      => ['x' => 380, 'y' => 334],
        'special_women_illness'    => ['x' => 380, 'y' => 334],
    'chk_completion_masters'  => ['x' => 323, 'y' => 378],
    'chk_bar_board_exam'      => ['x' => 323, 'y' => 393],
    'chk_monetization'         => ['x' => 323, 'y' => 422],
    'chk_terminal_leave'       => ['x' => 323, 'y' => 436],

    // ── Section 6.C / 6.D ────────────────────────────────────────
    'working_days_applied'    => ['x' => 90, 'y' => 468],
    'inclusive_dates'          => ['x' => 90, 'y' => 500],
        'chk_commutation_not_requested' => ['x' => 323,  'y' => 468],
        'chk_commutation_requested'     => ['x' => 323, 'y' => 483],
            'applicant_signature_name' => ['x' => 375, 'y' => 500,],
            'applicant_signature'      => ['x' => 355, 'y' => 480, 'width' => 130, 'height' => 28],

    // ── Section 7.A: Certification of Leave Credits ─────────────
    'certification_as_of'      => ['x' => 165, 'y' => 566],
    // Small grid: columns Vacation Leave / Sick Leave
    'vl_total_earned'          => ['x' => 165, 'y' => 592],
    'sl_total_earned'          => ['x' => 240, 'y' => 592],
    'vl_less_this_application' => ['x' => 165, 'y' => 602],
    'sl_less_this_application' => ['x' => 240, 'y' => 602],
    'vl_balance'                => ['x' => 240, 'y' => 613],
    'sl_balance'                => ['x' => 165, 'y' => 613],

    // ── Section 7.B: Recommendation ─────────────────────────────
    'chk_for_approval'         => ['x' => 325, 'y' => 566],
    'chk_for_disapproval'      => ['x' => 325, 'y' => 581],
    'disapproval_reason_line'  => ['x' => 335, 'y' => 589],
    'disapproval_reason_l3'    => ['x' => 335, 'y' => 601],
    'disapproval_reason_l4'    => ['x' => 335, 'y' => 612],
    'authorized_signatory_signature' => ['x' => 350, 'y' => 616, 'width' => 130, 'height' => 28],
    'authorized_signatory_name' => ['x' => 382, 'y' => 633],
    'authorized_signatory_designation' => ['x' => 387, 'y' => 644],

    // ── Section 7.C: Approved For ────────────────────────────────
    'approved_days_with_pay'    => ['x' => 85,  'y' => 682],
    'approved_days_without_pay' => ['x' => 85,  'y' => 692],
    'approved_days_others'      => ['x' => 85,  'y' => 703],

    // ── Section 7.D: Disapproved Due To ─────────────────────────
    'disapproved_reason_l1' => ['x' => 337, 'y' => 681],
    'disapproved_reason_l2' => ['x' => 337, 'y' => 691],
    'disapproved_reason_l3' => ['x' => 337, 'y' => 702],

];
