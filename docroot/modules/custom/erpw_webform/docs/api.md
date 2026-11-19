# eRPW Webform — API Reference

## `GET /api/ussd-services/export`

Returns all approved service provider submissions for the active domain, serialised as a JSON array. This endpoint is consumed by the USSD integration and any external system (e.g. Primero) that needs to sync service directory data.

### Authentication

None required. The endpoint is publicly accessible.

### Query parameters

Both parameters are optional. When omitted, all approved submissions for the active domain are returned.

| Parameter | Type | Description |
|---|---|---|
| `webform_id` | string | Filter by service type. Must be one of the webform machine names listed in [Service Types](#service-types). |
| `webform_submission_organisation_filter` | integer | Filter by organisation node ID. |

**Example with filters:**
```
GET /api/ussd-services/export?webform_id=sl_medical_services&webform_submission_organisation_filter=42
```

### Pagination

None. All matching records are returned in a single response.

### Response format

`Content-Type: application/json`

The response is a JSON array. Each element represents one approved service submission and contains a single key `ussd_services_data` whose value is a JSON-encoded string of the service data fields.

```json
[
  {
    "ussd_services_data": "{\"Service Type\":\"Medical Services\",\"Location\":\"Gbane Chiefdom, Kono, Sierra Leone.\",\"Organisation\":\"Example Health NGO\",\"Age Group\":[\"Below 18\",\"18+\"],\"Gender\":[\"Female\",\"Male\"],\"Target Group\":[\"General Population\",\"Refugees\"],\"Service Mode\":[\"In Person\"],\"Transportation Available\":[\"Provide Transport Fare\"],\"Has the ability to support people with the following:\":[\"Trouble seeing\",\"Trouble hearing\"],\"Medical Services Provided\":[\"PEP\",\"Family Planning\",\"Antenatal Care\"],\"Add More Details\":\"Referrals accepted Mon–Fri.\"}"
  }
]
```

To read a service record, parse `ussd_services_data` as JSON:

```js
const rows = await fetch('/api/ussd-services/export').then(r => r.json());
const services = rows.map(row => JSON.parse(row.ussd_services_data));
```

### Response fields

The parsed `ussd_services_data` object contains the following fields. All fields are optional — a field is omitted from the object when the submitter left it blank.

Fields are sorted alphabetically by key in the response.

| Field | Type | Present in |
|---|---|---|
| `Service Type` | string | All service types |
| `Location` | string | All service types |
| `Organisation` | string | All service types |
| `Age Group` | string[] | All except Hotline |
| `Gender` | string[] | All except Hotline |
| `Target Group` | string[] | All except Hotline |
| `Service Mode` | string[] | All except Hotline |
| `Transportation Available` | string[] | All except Hotline and Police Services |
| `Has the ability to support people with the following:` | string[] | All except Hotline |
| `Medical Services Provided` | string[] | Medical Services only |
| `Psychosocial Support Provided` | string[] | Psychosocial Support only |
| `Legal Services Provided` | string[] | Legal Aid only |
| `Police Services` | string[] | Police Services only |
| `Livelihood Services Provided` | string[] | Livelihood Services only |
| `Education or Training Services Provided` | string[] | Education/Training Services only |
| `Add More Details` | string | All except Hotline |
| `Name of focal point for referrals (first/last name)` | string | All service types |
| `Phone number of focal point` | string | All service types |
| `Email of focal point` | string | All service types |
| `Name of backup focal point for referrals (first/last name)` | string | All service types |
| `Phone number of backup focal point` | string | All service types |
| `Email of backup focal point` | string | All service types |
| `Phone number` | string | Hotline only |
| `Opening Times` | object | All service types |
| `Cost of Service` | string | All except Hotline |

#### `Location`

A human-readable location path built from up to four hierarchy levels plus country, separated by commas and terminated with a period. Levels are ordered from most specific to least specific.

```
"Gbane Chiefdom, Kono, Sierra Leone."
```

The location is stored as internal taxonomy term IDs in the database; this string is resolved at render time.

> **Note:** Primero p-codes are not currently included in the location value. See [open issue: add p-code to API response](#).

#### `Opening Times`

A structured object keyed by day (lowercase), with HTML paragraph strings as values.

```json
{
  "monday": "<p class='opening-hours-value'>Monday : 8 am - 5 pm</p>",
  "friday": "<p class='opening-hours-value'>Friday : 8 am - 5 pm</p>"
}
```

Days with no recorded hours are omitted.

---

## Service types

Each service type corresponds to one webform. The `webform_id` query parameter accepts the machine names in the left column.

| `webform_id` | Service Type label |
|---|---|
| `sl_case_management` | Case Management |
| `sl_education_training_services` | Education/Training Services |
| `sl_hotline` | Hotline |
| `sl_legal_aid` | Legal Aid |
| `sl_livelihood_services` | Livelihood Services |
| `sl_medical_services` | Medical Services |
| `sl_police_services` | Police Services |
| `sl_protection_shelter` | Protection/Shelter |
| `sl_psychosocial_support` | Psychosocial Support |

---

## Valid field values

### Service Mode
`In Person` · `Remote`

### Age Group
`Below 18` · `18+`

### Gender
`Female` · `Male`

### Target Group
`General Population` · `Refugees` · `Internally Displaced Persons (IDPs)`

### Transportation Available
`Pick up & Drop Off` · `Provide Transport Fare`

### Has the ability to support people with the following
`Trouble speaking` · `Trouble hearing` · `Trouble seeing` · `Trouble concentrating or remembering` · `Trouble moving around`

### Medical Services Provided
`Medical Emergency` · `Surgical` · `Blood Transfusion` · `Treat Injuries` · `Prevent/Treat STIs` · `Reproductive Health Services` · `PEP` · `Family Planning` · `Antenatal Care` · `X-Rays/ Screening` · `VCCT` · `Medical Report`

> Free-text "Other" value is also accepted.

### Psychosocial Support Provided
`Psychological First Aid (PFA)` · `Counselling` · `Emotional Support` · `Mediation` · `Peer Support` · `Accompanying Survivor to court`

> Free-text "Other" value is also accepted.

### Legal Services Provided
`Legal Advice` · `Legal Representation` · `Court Monitoring` · `Prosecution`

> Free-text "Other" value is also accepted.

### Police Services
`Investigation` · `Prosecution` · `Referral for medical examination` · `Referral for medical treatment`

> Free-text "Other" value is also accepted.

### Livelihood Services Provided
`Trainings on Income Generating Activities (IGA)` · `Grants for small business` · `Loans for small business` · `Resettlement packages`

> Free-text "Other" value is also accepted.

### Education or Training Services Provided
`Enrollment in school or vocational training` · `Provide learning materials` · `Provide start-up kit`

> Free-text "Other" value is also accepted.

---

## Related endpoints

| Endpoint | Description |
|---|---|
| `GET /manage-services/export` | Admin-facing export. Includes additional fields: `Submitted By`, `Service Type Color`, `Service Type Icon`. Requires appropriate role. |
| `GET /api/erpw-webform-serviceslatestupdate/service` | Returns `sid` and `changed` timestamp of the most recently approved submission. Used for cache invalidation / incremental sync. |
| `GET /api/erpw-webform-serviceformsexporter` | Returns the structure (elements and options) of all service webforms. |

---

## Implementation notes

The endpoint is implemented as a Views REST export display (`rest_export_2`) on the `manage_webform_services` view. The field data is rendered by `Drupal\erpw_ussd_api\Plugin\views\field\UssdServicesData`.

Only submissions with workflow state `approve` are returned. Submissions in `draft`, `in_review`, or `reject` state are excluded.

Duplicate submissions (same organisation + same lowest-level location, non-rejected) are prevented at submit time by `Drupal\erpw_webform\Plugin\WebformHandler\DuplicateSubmissionWebformHandler`. Historical duplicates may still exist and must be resolved manually via the admin interface.
