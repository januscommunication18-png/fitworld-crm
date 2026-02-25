<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class SidebarTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use host 1 by default
        $hostId = 1;

        $translations = [
            // Section Labels
            [
                'key' => 'nav.section.main',
                'en' => 'Main',
                'fr' => 'Principal',
                'de' => 'Hauptmenü',
                'es' => 'Principal',
            ],
            [
                'key' => 'nav.section.commerce',
                'en' => 'Commerce',
                'fr' => 'Commerce',
                'de' => 'Handel',
                'es' => 'Comercio',
            ],
            [
                'key' => 'nav.section.system',
                'en' => 'System',
                'fr' => 'Système',
                'de' => 'System',
                'es' => 'Sistema',
            ],

            // Dashboard
            [
                'key' => 'nav.dashboard',
                'en' => 'Dashboard',
                'fr' => 'Tableau de bord',
                'de' => 'Dashboard',
                'es' => 'Panel de control',
            ],
            [
                'key' => 'nav.dashboard.overview',
                'en' => 'Overview',
                'fr' => 'Aperçu',
                'de' => 'Übersicht',
                'es' => 'Resumen',
            ],
            [
                'key' => 'nav.dashboard.todays_classes',
                'en' => "Today's Classes",
                'fr' => "Cours d'aujourd'hui",
                'de' => 'Heutige Kurse',
                'es' => 'Clases de hoy',
            ],
            [
                'key' => 'nav.dashboard.upcoming_bookings',
                'en' => 'Upcoming Bookings',
                'fr' => 'Réservations à venir',
                'de' => 'Kommende Buchungen',
                'es' => 'Próximas reservas',
            ],
            [
                'key' => 'nav.dashboard.alerts',
                'en' => 'Alerts & Reminders',
                'fr' => 'Alertes et rappels',
                'de' => 'Warnungen & Erinnerungen',
                'es' => 'Alertas y recordatorios',
            ],

            // Schedule
            [
                'key' => 'nav.schedule',
                'en' => 'Schedule',
                'fr' => 'Calendrier',
                'de' => 'Zeitplan',
                'es' => 'Horario',
            ],
            [
                'key' => 'nav.schedule.calendar',
                'en' => 'Calendar View',
                'fr' => 'Vue calendrier',
                'de' => 'Kalenderansicht',
                'es' => 'Vista de calendario',
            ],
            [
                'key' => 'nav.schedule.class_sessions',
                'en' => 'Class Sessions',
                'fr' => 'Sessions de cours',
                'de' => 'Kurssitzungen',
                'es' => 'Sesiones de clase',
            ],
            [
                'key' => 'nav.schedule.service_slots',
                'en' => 'Service Slots',
                'fr' => 'Créneaux de service',
                'de' => 'Service-Slots',
                'es' => 'Horarios de servicio',
            ],
            [
                'key' => 'nav.schedule.membership_sessions',
                'en' => 'Membership Sessions',
                'fr' => 'Sessions membres',
                'de' => 'Mitgliedschaftssitzungen',
                'es' => 'Sesiones de membresía',
            ],

            // Bookings
            [
                'key' => 'nav.bookings',
                'en' => 'Bookings',
                'fr' => 'Réservations',
                'de' => 'Buchungen',
                'es' => 'Reservas',
            ],
            [
                'key' => 'nav.bookings.all',
                'en' => 'All Bookings',
                'fr' => 'Toutes les réservations',
                'de' => 'Alle Buchungen',
                'es' => 'Todas las reservas',
            ],
            [
                'key' => 'nav.bookings.my_bookings',
                'en' => 'My Class Bookings',
                'fr' => 'Mes réservations',
                'de' => 'Meine Buchungen',
                'es' => 'Mis reservas de clase',
            ],
            [
                'key' => 'nav.bookings.upcoming',
                'en' => 'Upcoming',
                'fr' => 'À venir',
                'de' => 'Kommende',
                'es' => 'Próximas',
            ],
            [
                'key' => 'nav.bookings.cancellations',
                'en' => 'Cancellations',
                'fr' => 'Annulations',
                'de' => 'Stornierungen',
                'es' => 'Cancelaciones',
            ],
            [
                'key' => 'nav.bookings.no_shows',
                'en' => 'No-Shows',
                'fr' => 'Absences',
                'de' => 'Nichterscheinen',
                'es' => 'No presentados',
            ],
            [
                'key' => 'nav.bookings.requests',
                'en' => 'Requests',
                'fr' => 'Demandes',
                'de' => 'Anfragen',
                'es' => 'Solicitudes',
            ],
            [
                'key' => 'nav.bookings.waitlist',
                'en' => 'Waitlist',
                'fr' => 'Liste d\'attente',
                'de' => 'Warteliste',
                'es' => 'Lista de espera',
            ],

            // Clients
            [
                'key' => 'nav.clients',
                'en' => 'Clients',
                'fr' => 'Clients',
                'de' => 'Kunden',
                'es' => 'Clientes',
            ],
            [
                'key' => 'nav.clients.all',
                'en' => 'All Clients',
                'fr' => 'Tous les clients',
                'de' => 'Alle Kunden',
                'es' => 'Todos los clientes',
            ],
            [
                'key' => 'nav.clients.leads',
                'en' => 'Leads',
                'fr' => 'Prospects',
                'de' => 'Leads',
                'es' => 'Prospectos',
            ],
            [
                'key' => 'nav.clients.members',
                'en' => 'Members',
                'fr' => 'Membres',
                'de' => 'Mitglieder',
                'es' => 'Miembros',
            ],
            [
                'key' => 'nav.clients.at_risk',
                'en' => 'At-Risk',
                'fr' => 'À risque',
                'de' => 'Gefährdet',
                'es' => 'En riesgo',
            ],
            [
                'key' => 'nav.clients.tags',
                'en' => 'Tags',
                'fr' => 'Étiquettes',
                'de' => 'Tags',
                'es' => 'Etiquetas',
            ],
            [
                'key' => 'nav.clients.lead_magnet',
                'en' => 'Lead Magnet',
                'fr' => 'Aimant à prospects',
                'de' => 'Lead-Magnet',
                'es' => 'Imán de prospectos',
            ],

            // Help Desk
            [
                'key' => 'nav.helpdesk',
                'en' => 'Help Desk',
                'fr' => 'Service d\'aide',
                'de' => 'Helpdesk',
                'es' => 'Mesa de ayuda',
            ],

            // Instructors
            [
                'key' => 'nav.instructors',
                'en' => 'Instructors',
                'fr' => 'Instructeurs',
                'de' => 'Kursleiter',
                'es' => 'Instructores',
            ],
            [
                'key' => 'nav.instructors.list',
                'en' => 'Instructor List',
                'fr' => 'Liste des instructeurs',
                'de' => 'Kursleiterliste',
                'es' => 'Lista de instructores',
            ],
            [
                'key' => 'nav.instructors.availability',
                'en' => 'Availability',
                'fr' => 'Disponibilité',
                'de' => 'Verfügbarkeit',
                'es' => 'Disponibilidad',
            ],
            [
                'key' => 'nav.instructors.assignments',
                'en' => 'Assignments',
                'fr' => 'Affectations',
                'de' => 'Zuweisungen',
                'es' => 'Asignaciones',
            ],
            [
                'key' => 'nav.instructors.payouts',
                'en' => 'Payouts',
                'fr' => 'Paiements',
                'de' => 'Auszahlungen',
                'es' => 'Pagos',
            ],

            // Catalog
            [
                'key' => 'nav.catalog',
                'en' => 'Catalog',
                'fr' => 'Catalogue',
                'de' => 'Katalog',
                'es' => 'Catálogo',
            ],

            // Rentals
            [
                'key' => 'nav.rentals',
                'en' => 'Rentals',
                'fr' => 'Locations',
                'de' => 'Vermietungen',
                'es' => 'Alquileres',
            ],
            [
                'key' => 'nav.rentals.all_items',
                'en' => 'All Items',
                'fr' => 'Tous les articles',
                'de' => 'Alle Artikel',
                'es' => 'Todos los artículos',
            ],
            [
                'key' => 'nav.rentals.fulfillment',
                'en' => 'Fulfillment',
                'fr' => 'Traitement',
                'de' => 'Erfüllung',
                'es' => 'Cumplimiento',
            ],
            [
                'key' => 'nav.rentals.create_invoice',
                'en' => 'Create Invoice',
                'fr' => 'Créer une facture',
                'de' => 'Rechnung erstellen',
                'es' => 'Crear factura',
            ],

            // Marketing
            [
                'key' => 'nav.marketing',
                'en' => 'Marketing',
                'fr' => 'Marketing',
                'de' => 'Marketing',
                'es' => 'Marketing',
            ],
            [
                'key' => 'nav.marketing.segments',
                'en' => 'Segments',
                'fr' => 'Segments',
                'de' => 'Segmente',
                'es' => 'Segmentos',
            ],
            [
                'key' => 'nav.marketing.offers',
                'en' => 'Offers',
                'fr' => 'Offres',
                'de' => 'Angebote',
                'es' => 'Ofertas',
            ],
            [
                'key' => 'nav.marketing.campaigns',
                'en' => 'Campaigns',
                'fr' => 'Campagnes',
                'de' => 'Kampagnen',
                'es' => 'Campañas',
            ],
            [
                'key' => 'nav.marketing.referrals',
                'en' => 'Referrals',
                'fr' => 'Parrainages',
                'de' => 'Empfehlungen',
                'es' => 'Referencias',
            ],

            // Insights
            [
                'key' => 'nav.insights',
                'en' => 'Insights',
                'fr' => 'Statistiques',
                'de' => 'Einblicke',
                'es' => 'Estadísticas',
            ],
            [
                'key' => 'nav.insights.attendance',
                'en' => 'Attendance',
                'fr' => 'Présence',
                'de' => 'Anwesenheit',
                'es' => 'Asistencia',
            ],
            [
                'key' => 'nav.insights.revenue',
                'en' => 'Revenue',
                'fr' => 'Revenus',
                'de' => 'Umsatz',
                'es' => 'Ingresos',
            ],
            [
                'key' => 'nav.insights.class_performance',
                'en' => 'Class Performance',
                'fr' => 'Performance des cours',
                'de' => 'Kursleistung',
                'es' => 'Rendimiento de clases',
            ],
            [
                'key' => 'nav.insights.retention',
                'en' => 'Retention',
                'fr' => 'Rétention',
                'de' => 'Kundenbindung',
                'es' => 'Retención',
            ],

            // Payments
            [
                'key' => 'nav.payments',
                'en' => 'Payments',
                'fr' => 'Paiements',
                'de' => 'Zahlungen',
                'es' => 'Pagos',
            ],
            [
                'key' => 'nav.payments.transactions',
                'en' => 'Transactions',
                'fr' => 'Transactions',
                'de' => 'Transaktionen',
                'es' => 'Transacciones',
            ],
            [
                'key' => 'nav.payments.subscriptions',
                'en' => 'Subscriptions',
                'fr' => 'Abonnements',
                'de' => 'Abonnements',
                'es' => 'Suscripciones',
            ],
            [
                'key' => 'nav.payments.payouts',
                'en' => 'Payouts',
                'fr' => 'Versements',
                'de' => 'Auszahlungen',
                'es' => 'Pagos',
            ],
            [
                'key' => 'nav.payments.refunds',
                'en' => 'Refunds',
                'fr' => 'Remboursements',
                'de' => 'Rückerstattungen',
                'es' => 'Reembolsos',
            ],

            // Settings
            [
                'key' => 'nav.settings',
                'en' => 'Settings',
                'fr' => 'Paramètres',
                'de' => 'Einstellungen',
                'es' => 'Configuración',
            ],

            // Sign Out
            [
                'key' => 'nav.sign_out',
                'en' => 'Sign Out',
                'fr' => 'Déconnexion',
                'de' => 'Abmelden',
                'es' => 'Cerrar sesión',
            ],

            // Common badges/labels
            [
                'key' => 'nav.badge.soon',
                'en' => 'Soon',
                'fr' => 'Bientôt',
                'de' => 'Bald',
                'es' => 'Pronto',
            ],
            [
                'key' => 'nav.badge.later',
                'en' => 'Later',
                'fr' => 'Plus tard',
                'de' => 'Später',
                'es' => 'Más tarde',
            ],
        ];

        foreach ($translations as $item) {
            Translation::updateOrCreate(
                [
                    'host_id' => $hostId,
                    'translation_key' => $item['key'],
                ],
                [
                    'category' => 'general_content',
                    'page_context' => 'sidebar',
                    'value_en' => $item['en'],
                    'value_fr' => $item['fr'],
                    'value_de' => $item['de'],
                    'value_es' => $item['es'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Created ' . count($translations) . ' sidebar translations for host ' . $hostId);
    }
}
