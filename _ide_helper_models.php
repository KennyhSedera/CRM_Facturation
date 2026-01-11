<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $article_id
 * @property int $user_id
 * @property string|null $article_source
 * @property string|null $article_unité
 * @property string $selling_price
 * @property string $article_name
 * @property string|null $article_reference
 * @property string $article_tva
 * @property int $quantity_stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleTva($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleUnité($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereQuantityStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUserId($value)
 */
	class Article extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $client_id
 * @property string $client_email
 * @property string $client_name
 * @property string|null $client_adress
 * @property string|null $client_cin
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $client_phone
 * @property string|null $client_city
 * @property string|null $client_country
 * @property string $client_status
 * @property string|null $client_note
 * @property string|null $client_reference
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quote> $quotes
 * @property-read int|null $quotes_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientAdress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientCin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUserId($value)
 */
	class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $company_id
 * @property string $company_email
 * @property string $company_name
 * @property string|null $company_logo
 * @property string $plan_status
 * @property string|null $company_description
 * @property string|null $company_phone
 * @property string|null $company_website
 * @property string|null $company_address
 * @property string|null $company_city
 * @property string|null $company_postal_code
 * @property string $company_country
 * @property string|null $company_registration_number
 * @property string|null $company_tax_number
 * @property \Illuminate\Support\Carbon|null $plan_start_date
 * @property \Illuminate\Support\Carbon|null $plan_end_date
 * @property bool $is_active
 * @property string $company_currency
 * @property string $company_timezone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $client_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereClientCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyPostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCompanyWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePlanEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePlanStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePlanStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Quote|null $quote
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 */
	class InvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $mvt_id
 * @property int $article_id
 * @property int $user_id
 * @property string $mvtType
 * @property int $mvt_quantity
 * @property string $mvt_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Article|null $article
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereMvtType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MvtArticle whereUserId($value)
 */
	class MvtArticle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $payment_id
 * @property int $company_id
 * @property int $user_id
 * @property string $payment_reference
 * @property string $payment_method
 * @property string $plan_type
 * @property string $action_type
 * @property float $amount
 * @property string $currency
 * @property string|null $transaction_id
 * @property string|null $transaction_proof
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property int|null $confirmed_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User|null $confirmedBy
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment confirmed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePlanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionProof($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserId($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $quote_id
 * @property int $user_id
 * @property int $client_id
 * @property int $vendeur_id
 * @property string $total_amount
 * @property string|null $mode_paiement
 * @property string $quote_status
 * @property string $quote_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereModePaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereQuoteDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereQuoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereQuoteStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quote whereVendeurId($value)
 */
	class Quote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Article|null $article
 * @property-read \App\Models\Quote|null $quote
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuoteItem query()
 */
	class QuoteItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\User|null $agent
 * @property-read string $age
 * @property-read string $priority_emoji
 * @property-read string $priority_label
 * @property-read string|null $resolution_time
 * @property-read string $status_emoji
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketMessage> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket assignedTo(int $agentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket byCategory(string $category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket byPriority(string $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket closed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket forUser(int $telegramId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket inProgress()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket old()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket unassigned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket urgent()
 */
	class Ticket extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read string $time_ago
 * @property-read string $type_emoji
 * @property-read string $type_label
 * @property-read \App\Models\Ticket|null $ticket
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketMessage query()
 */
	class TicketMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property string $user_role
 * @property string|null $telegram_id
 * @property string|null $avatar
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelegramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserRole($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTelegram newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTelegram newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTelegram query()
 */
	class UserTelegram extends \Eloquent {}
}

