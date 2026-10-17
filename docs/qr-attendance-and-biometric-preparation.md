# QR attendance and biometric preparation

## Student QR cards and USB scanners

Each student has a 256-bit random token. The database stores an encrypted copy for printing and a SHA-256 digest for lookup; neither token nor digest is serialized with a student model. Existing students receive tokens when the QR-token migration is deployed, and new students receive one when created. Printing an ID card does not rotate its token. Replacing or revoking a token invalidates cards already printed with the old value.

The QR contains only the opaque token, not a student ID, school ID, name, or attendance URL. QR scanning is not proof of physical identity: a QR image can be copied.

The attendance scanner is intended for a USB QR reader operating as a keyboard/HID device. It does not request webcam access. The browser scanner field receives the scanned token like keyboard input and submits on Enter. For a reader configured to send Tab, select the Tab suffix option. The manual search fallback accepts a student name or admission number and applies the same tenant, enrollment, and class authorization checks server-side.

The default `QR_ATTENDANCE_POLICY=mark_present` policy creates a Present record in the existing daily attendance session. A scan never updates an existing attendance row: manual records remain unchanged and are reported as duplicates. Set `QR_ATTENDANCE_POLICY=review` in `.env` to store a unique gate-entry event as `pending_review` without marking attendance. Authorized staff can review the event on the scanner page and use the existing attendance register to confirm or correct attendance. There is no exit-scan mode.

The event timestamp is stored in UTC and shown in the school's configured timezone; `Asia/Karachi` is the fallback.

### Local XAMPP test

1. Start Apache and MySQL in XAMPP and configure the existing app's `.env` database settings for that MySQL instance. Keep the app key configured because student QR tokens are encrypted at rest.
2. Install the locked PHP dependencies with `composer install` if they are not already installed.
3. Back up the database before deploying. To add QR support to an existing database, apply the two new migrations through the normal deployment process. They add token columns and a QR scan-event table; they do not create another attendance table. The token migration assigns a unique token to existing students. Do not run migrations against production without the app key and a verified backup.
4. Start the app with `php artisan serve` (or the existing XAMPP virtual host) and sign in as a school administrator, principal, teacher, or staff member with attendance access.
5. Open **School Admin → Attendance → USB QR Scanner**. Press Enter or click the scan input, paste/type a student's printed-card token, and press Enter. Verify the student details and success/duplicate result. The browser keyboard test does not require a scanner.
6. Open a student's profile as a school administrator or principal and choose **Student QR ID card**. Use **Print card** or the browser print dialog; reprinting preserves the token. Use **Replace QR** or **Revoke QR** to invalidate old cards.
7. A supported USB-HID reader should be plugged into the computer running the browser, configured to send the QR data as keyboard input followed by Enter (or Tab), then tested in the scanner field. Check that it outputs the full 64-character token without extra prefixes, suffixes, or altered characters.
8. Check the daily and monthly attendance registers after scanning. Existing manually entered attendance must remain unchanged.

The application makes no camera permission request on this page. If testing through a network hostname instead of `localhost`, use HTTPS if browser policy or other site features require it. The scanner keyboard input itself works without camera permission.

## Scanner purchase checks

The referenced scanner image or a model number was not available in the project, so no specific device has been verified. Before buying, obtain the exact model/revision and confirm:

- It reads **QR codes** (not merely 1D barcodes) at the printed card's size and contrast.
- It supports USB keyboard-wedge/HID mode on Windows and sends decoded data to the focused browser input.
- Its configuration manual explicitly documents Enter and/or Tab suffix selection and disabling prefixes, formatting, and automatic case conversion.
- It can decode the full 64-character hexadecimal token without truncation.
- It works without proprietary software, cloud services, or a special browser plug-in.
- The seller permits a return or trial so the exact hardware can be tested with a locally generated student card and the target PC/USB port.

These are requirements, not claims about an unverified model.

## Fingerprint staff attendance: not yet connected

The existing staff workflow is `staff_attendance`: it stores one daily status, check-in and check-out time per employee, and has a unique key on school, employee, and date. The current workflow does not provide biometric device registration, device-user mappings, raw punch-event ingestion, or import/sync status. No biometric hardware, SDK, protocol, or documented sample export has been supplied. No fingerprint image/template is collected or stored, and no live-device client or sample importer has been implemented.

Before choosing a fingerprint terminal, get the exact model, firmware, interface, and vendor protocol/SDK documentation. Verify all of the following with the supplier:

- A documented LAN API/protocol, supported SDK, or UTF-8 CSV/Excel punch-log export; exact SDK languages, supported Windows/PHP versions, licensing, and authentication.
- A stable device identifier and a stable device-side user/enrollment ID that can be mapped uniquely to a staff record and school.
- An event-level log with a vendor event/punch ID or enough documented fields to deduplicate (device ID, user ID, exact timestamp, event type).
- Timestamp timezone, clock-sync behavior, DST behavior, and whether entry/exit direction is reliably reported.
- Whether logs can be read incrementally, acknowledged, and safely retried after a network interruption without losing or changing events.
- A supported communication path from the XAMPP host (USB, serial, LAN TCP/IP, or file export), firewall/port requirements, and whether the device requires a vendor Windows service.
- Fingerprint matching/enrollment performed securely on the device, with no requirement for the Laravel app to receive biometric images or templates.
- A test unit and a documented sample export/API response from that exact firmware before application integration is scoped.

Do not infer compatibility from a PHP/Laravel package name or from another terminal in the same product family. If a future device emits multiple punches per day, map its documented events deliberately to the current daily workflow (or approve a workflow/schema change); do not silently overwrite the existing daily attendance record. Device management must remain school-scoped, with school-admin/principal access and unique school/device and device/user mappings.
