# Branch Business Rules

## Relationships

- 1 Organization dapat memiliki banyak Branch.
- Branch wajib memiliki Organization.

## Columns

- `branch_code` harus unik dalam satu Organization (unique per organization).

## Status

| Value      | Description                     |
|------------|---------------------------------|
| `active`   | Branch aktif dan beroperasi     |
| `inactive` | Branch dinonaktifkan            |

- Branch dapat dinonaktifkan kapan saja.

## Delete Rules

Branch tidak boleh dihapus apabila masih memiliki:

- User
- Patient
- Appointment
- Inventory
- Finance Transaction
