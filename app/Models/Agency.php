<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agency extends Authenticatable
{
    use HasFactory;

    protected $table = 'agencies';

    protected $fillable = [
        'hub_id',
        'name',
        'code',
        'country',
        'city',
        'address',
        'phone',
        'phone_secondary',
        'primary_contact',
        'email',
        'notification_emails',
        'manifest_fields',
        'datasheet_fields',
        'operational_notes',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notification_emails' => 'array',
        'manifest_fields' => 'array',
        'datasheet_fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($agency) {
            if (empty($agency->address)) {
                $agency->address = ($agency->city ?? 'Central') . ' Cargo Hub';
            }
            if (empty($agency->phone)) {
                $agency->phone = '+000-0000-000';
            }
            if (empty($agency->password)) {
                $agency->password = bcrypt('Agency@12345');
            }
        });
    }

    public function hubs()
    {
        return $this->belongsToMany(OverseasHub::class, 'agency_hub', 'agency_id', 'hub_id')->withTimestamps();
    }

    public function hub()
    {
        return $this->belongsTo(OverseasHub::class, 'hub_id');
    }

    public function getPrimaryHubAttribute()
    {
        return $this->hubs->first() ?? $this->hub;
    }

    public function rates()
    {
        return $this->hasMany(InternationalRate::class, 'agency_id');
    }

    public function getContactPersonAttribute()
    {
        return $this->primary_contact;
    }

    public function setContactPersonAttribute($value)
    {
        $this->attributes['primary_contact'] = $value;
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['is_active'] = (strtolower((string)$value) === 'active' || $value === true || $value === 1 || $value === '1');
    }

    public function staff()
    {
        return $this->hasMany(AgencyStaff::class, 'agency_id');
    }

    public function manifests()
    {
        return $this->hasMany(Manifest::class, 'agency_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'current_agency_id');
    }

    public function getArrivedShipments()
    {
        return $this->shipments()->whereNotNull('arrived_at_agency')->whereNull('departed_from_agency');
    }

    public function getDepartedShipments()
    {
        return $this->shipments()->whereNotNull('departed_from_agency');
    }

    /**
     * Get all pre-defined email addresses for this agency (including primary and notification emails)
     */
    public function getAllNotificationEmails(): array
    {
        $emails = [];
        if (!empty($this->email)) {
            $emails[] = strtolower(trim($this->email));
        }

        if (!empty($this->notification_emails) && is_array($this->notification_emails)) {
            foreach ($this->notification_emails as $email) {
                $email = strtolower(trim($email));
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $emails, true)) {
                    $emails[] = $email;
                }
            }
        }

        return $emails;
    }

    /**
     * Standard manifest fields for this agency with default fallback
     */
    public function getManifestFields(): array
    {
        if (!empty($this->manifest_fields) && is_array($this->manifest_fields)) {
            return $this->manifest_fields;
        }

        return [
            'hawb_number' => 'HAWB Number',
            'tracking_number' => 'Tracking Number',
            'receiver_name' => 'Consignee Name',
            'receiver_city' => 'Destination City',
            'receiver_country' => 'Country',
            'actual_weight' => 'Weight (kg)',
            'package_type' => 'Package Type',
            'last_mile_carrier_name' => 'Last Mile Carrier',
        ];
    }

    /**
     * Standard data sheet fields for this agency with default fallback
     */
    public function getDataSheetFields(): array
    {
        if (!empty($this->datasheet_fields) && is_array($this->datasheet_fields)) {
            return $this->datasheet_fields;
        }

        return [
            'hawb_number' => 'HAWB Number',
            'tracking_number' => 'Tracking Number',
            'mawb_number' => 'MAWB Number',
            'sender_name' => 'Shipper Name',
            'sender_phone' => 'Shipper Phone',
            'sender_address' => 'Shipper Address',
            'receiver_name' => 'Consignee Name',
            'receiver_phone' => 'Consignee Phone',
            'receiver_address' => 'Consignee Street Address',
            'receiver_city' => 'City',
            'receiver_state' => 'State / Province',
            'receiver_postal_code' => 'Postal Code',
            'receiver_country' => 'Country',
            'receiver_tax_id' => 'Tax ID / VAT',
            'actual_weight' => 'Actual Weight (kg)',
            'chargeable_weight' => 'Chargeable Weight (kg)',
            'length' => 'Length (cm)',
            'width' => 'Width (cm)',
            'height' => 'Height (cm)',
            'description' => 'Goods Description',
            'package_type' => 'Package Type',
            'customs_mode' => 'Customs Mode (DDP/DDU)',
            'last_mile_carrier_name' => 'Last Mile Carrier',
            'created_at' => 'Booking Date',
        ];
    }
}