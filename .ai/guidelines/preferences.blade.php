<p>
    Preferences: Use PHP backed enums (App\Enums\*) for all status/type fields instead of database ENUM columns. Persist enum values as strings in the database and cast using native PHP enums on related models via the <code>$casts</code> array.
</p>

