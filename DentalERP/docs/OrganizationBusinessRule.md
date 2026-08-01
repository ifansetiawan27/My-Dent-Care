# Organization Business Rules

## Relationships

- 1 Organization dapat memiliki banyak Branch.
- 1 Organization memiliki banyak User.

## Columns

- `company_code` wajib unik di seluruh sistem.
- `timezone` wajib diisi.
- `currency` wajib diisi.

## Status

| Value      | Description                          |
|------------|--------------------------------------|
| `active`   | Organization aktif dan beroperasi    |
| `inactive` | Organization tidak aktif             |

## Delete Rules

- Organization tidak boleh dihapus apabila masih memiliki Branch aktif.
