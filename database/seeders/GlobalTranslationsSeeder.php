<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class GlobalTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates global translations (host_id = NULL) that apply to all studios as defaults.
     */
    public function run(): void
    {
        $translations = $this->getTranslations();

        foreach ($translations as $translation) {
            Translation::updateOrCreate(
                [
                    'host_id' => null,
                    'translation_key' => $translation['translation_key'],
                ],
                [
                    'category' => $translation['category'],
                    'page_context' => $translation['page_context'],
                    'value_en' => $translation['value_en'],
                    'value_fr' => $translation['value_fr'] ?? null,
                    'value_de' => $translation['value_de'] ?? null,
                    'value_es' => $translation['value_es'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Global translations seeded successfully! Total: ' . count($translations));
    }

    /**
     * Get all global translations to seed.
     */
    protected function getTranslations(): array
    {
        return array_merge(
            $this->getNavigationTranslations(),
            $this->getButtonTranslations(),
            $this->getMessageTranslations(),
            $this->getPageTitleTranslations(),
            $this->getFieldLabelTranslations(),
            $this->getBookingTranslations(),
            $this->getDashboardTranslations(),
            $this->getClientsTranslations(),
            $this->getScheduleTranslations(),
            $this->getInstructorTranslations(),
            $this->getPaymentTranslations(),
            $this->getSettingsTranslations(),
            $this->getSubdomainTranslations(),
            $this->getMemberPortalTranslations(),
            $this->getCommonTranslations(),
            $this->getCatalogTranslations(),
        );
    }

    /**
     * Navigation/Sidebar translations.
     */
    protected function getNavigationTranslations(): array
    {
        return [
            // Section headers
            ['translation_key' => 'nav.section.main', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Main', 'value_fr' => 'Principal', 'value_de' => 'Hauptmenü', 'value_es' => 'Principal'],
            ['translation_key' => 'nav.section.commerce', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Commerce', 'value_fr' => 'Commerce', 'value_de' => 'Handel', 'value_es' => 'Comercio'],
            ['translation_key' => 'nav.section.system', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'System', 'value_fr' => 'Système', 'value_de' => 'System', 'value_es' => 'Sistema'],

            // Dashboard
            ['translation_key' => 'nav.dashboard', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Dashboard', 'value_fr' => 'Tableau de bord', 'value_de' => 'Dashboard', 'value_es' => 'Panel'],
            ['translation_key' => 'nav.dashboard.overview', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Overview', 'value_fr' => 'Aperçu', 'value_de' => 'Übersicht', 'value_es' => 'Resumen'],
            ['translation_key' => 'nav.dashboard.todays_classes', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => "Today's Classes", 'value_fr' => "Cours d'aujourd'hui", 'value_de' => 'Heutige Kurse', 'value_es' => 'Clases de hoy'],
            ['translation_key' => 'nav.dashboard.upcoming_bookings', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Upcoming Bookings', 'value_fr' => 'Réservations à venir', 'value_de' => 'Kommende Buchungen', 'value_es' => 'Próximas reservas'],
            ['translation_key' => 'nav.dashboard.alerts', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Alerts & Reminders', 'value_fr' => 'Alertes et rappels', 'value_de' => 'Warnungen & Erinnerungen', 'value_es' => 'Alertas y recordatorios'],

            // Schedule
            ['translation_key' => 'nav.schedule', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Schedule', 'value_fr' => 'Emploi du temps', 'value_de' => 'Zeitplan', 'value_es' => 'Horario'],
            ['translation_key' => 'nav.schedule.calendar', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Calendar View', 'value_fr' => 'Vue calendrier', 'value_de' => 'Kalenderansicht', 'value_es' => 'Vista de calendario'],
            ['translation_key' => 'nav.schedule.class_sessions', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Class Sessions', 'value_fr' => 'Sessions de cours', 'value_de' => 'Kurseinheiten', 'value_es' => 'Sesiones de clase'],
            ['translation_key' => 'nav.schedule.service_slots', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Service Slots', 'value_fr' => 'Créneaux de service', 'value_de' => 'Service-Slots', 'value_es' => 'Horarios de servicio'],
            ['translation_key' => 'nav.schedule.membership_sessions', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Membership Sessions', 'value_fr' => 'Sessions membres', 'value_de' => 'Mitgliedschaftssitzungen', 'value_es' => 'Sesiones de membresía'],

            // Bookings
            ['translation_key' => 'nav.bookings', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Bookings', 'value_fr' => 'Réservations', 'value_de' => 'Buchungen', 'value_es' => 'Reservas'],
            ['translation_key' => 'nav.bookings.all', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'All Bookings', 'value_fr' => 'Toutes les réservations', 'value_de' => 'Alle Buchungen', 'value_es' => 'Todas las reservas'],
            ['translation_key' => 'nav.bookings.my_bookings', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'My Class Bookings', 'value_fr' => 'Mes réservations de cours', 'value_de' => 'Meine Kursbuchungen', 'value_es' => 'Mis reservas de clase'],
            ['translation_key' => 'nav.bookings.upcoming', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Upcoming', 'value_fr' => 'À venir', 'value_de' => 'Bevorstehend', 'value_es' => 'Próximas'],
            ['translation_key' => 'nav.bookings.cancellations', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Cancellations', 'value_fr' => 'Annulations', 'value_de' => 'Stornierungen', 'value_es' => 'Cancelaciones'],
            ['translation_key' => 'nav.bookings.no_shows', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'No-Shows', 'value_fr' => 'Absences', 'value_de' => 'Nichterscheinen', 'value_es' => 'Ausencias'],
            ['translation_key' => 'nav.bookings.requests', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Requests', 'value_fr' => 'Demandes', 'value_de' => 'Anfragen', 'value_es' => 'Solicitudes'],
            ['translation_key' => 'nav.bookings.waitlist', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Waitlist', 'value_fr' => 'Liste d\'attente', 'value_de' => 'Warteliste', 'value_es' => 'Lista de espera'],

            // Clients
            ['translation_key' => 'nav.clients', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Clients', 'value_fr' => 'Clients', 'value_de' => 'Kunden', 'value_es' => 'Clientes'],
            ['translation_key' => 'nav.clients.all', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'All Clients', 'value_fr' => 'Tous les clients', 'value_de' => 'Alle Kunden', 'value_es' => 'Todos los clientes'],
            ['translation_key' => 'nav.clients.leads', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Leads', 'value_fr' => 'Prospects', 'value_de' => 'Leads', 'value_es' => 'Prospectos'],
            ['translation_key' => 'nav.clients.members', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Members', 'value_fr' => 'Membres', 'value_de' => 'Mitglieder', 'value_es' => 'Miembros'],
            ['translation_key' => 'nav.clients.at_risk', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'At-Risk', 'value_fr' => 'À risque', 'value_de' => 'Gefährdet', 'value_es' => 'En riesgo'],
            ['translation_key' => 'nav.clients.tags', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Tags', 'value_fr' => 'Étiquettes', 'value_de' => 'Tags', 'value_es' => 'Etiquetas'],
            ['translation_key' => 'nav.clients.lead_magnet', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Lead Magnet', 'value_fr' => 'Aimant à prospects', 'value_de' => 'Lead-Magnet', 'value_es' => 'Imán de leads'],

            // Helpdesk & Instructors
            ['translation_key' => 'nav.helpdesk', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Help Desk', 'value_fr' => 'Support', 'value_de' => 'Helpdesk', 'value_es' => 'Soporte'],
            ['translation_key' => 'nav.instructors', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Instructors', 'value_fr' => 'Instructeurs', 'value_de' => 'Trainer', 'value_es' => 'Instructores'],
            ['translation_key' => 'nav.instructors.list', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Instructor List', 'value_fr' => 'Liste des instructeurs', 'value_de' => 'Trainerliste', 'value_es' => 'Lista de instructores'],
            ['translation_key' => 'nav.instructors.availability', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Availability', 'value_fr' => 'Disponibilité', 'value_de' => 'Verfügbarkeit', 'value_es' => 'Disponibilidad'],
            ['translation_key' => 'nav.instructors.assignments', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Assignments', 'value_fr' => 'Affectations', 'value_de' => 'Zuweisungen', 'value_es' => 'Asignaciones'],
            ['translation_key' => 'nav.instructors.payouts', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Payouts', 'value_fr' => 'Paiements', 'value_de' => 'Auszahlungen', 'value_es' => 'Pagos'],

            // Catalog & Rentals
            ['translation_key' => 'nav.catalog', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Catalog', 'value_fr' => 'Catalogue', 'value_de' => 'Katalog', 'value_es' => 'Catálogo'],
            ['translation_key' => 'nav.rentals', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Rentals', 'value_fr' => 'Locations', 'value_de' => 'Vermietungen', 'value_es' => 'Alquileres'],
            ['translation_key' => 'nav.rentals.all_items', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'All Items', 'value_fr' => 'Tous les articles', 'value_de' => 'Alle Artikel', 'value_es' => 'Todos los artículos'],
            ['translation_key' => 'nav.rentals.fulfillment', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Fulfillment', 'value_fr' => 'Exécution', 'value_de' => 'Erfüllung', 'value_es' => 'Cumplimiento'],
            ['translation_key' => 'nav.rentals.create_invoice', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Create Invoice', 'value_fr' => 'Créer une facture', 'value_de' => 'Rechnung erstellen', 'value_es' => 'Crear factura'],

            // Marketing
            ['translation_key' => 'nav.marketing', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Marketing', 'value_fr' => 'Marketing', 'value_de' => 'Marketing', 'value_es' => 'Marketing'],
            ['translation_key' => 'nav.marketing.segments', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Segments', 'value_fr' => 'Segments', 'value_de' => 'Segmente', 'value_es' => 'Segmentos'],
            ['translation_key' => 'nav.marketing.offers', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Offers', 'value_fr' => 'Offres', 'value_de' => 'Angebote', 'value_es' => 'Ofertas'],
            ['translation_key' => 'nav.marketing.campaigns', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Campaigns', 'value_fr' => 'Campagnes', 'value_de' => 'Kampagnen', 'value_es' => 'Campañas'],
            ['translation_key' => 'nav.marketing.referrals', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Referrals', 'value_fr' => 'Parrainages', 'value_de' => 'Empfehlungen', 'value_es' => 'Referencias'],

            // Insights
            ['translation_key' => 'nav.insights', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Insights', 'value_fr' => 'Analyses', 'value_de' => 'Einblicke', 'value_es' => 'Análisis'],
            ['translation_key' => 'nav.insights.attendance', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Attendance', 'value_fr' => 'Présence', 'value_de' => 'Anwesenheit', 'value_es' => 'Asistencia'],
            ['translation_key' => 'nav.insights.revenue', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Revenue', 'value_fr' => 'Revenus', 'value_de' => 'Einnahmen', 'value_es' => 'Ingresos'],
            ['translation_key' => 'nav.insights.class_performance', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Class Performance', 'value_fr' => 'Performance des cours', 'value_de' => 'Kursleistung', 'value_es' => 'Rendimiento de clases'],
            ['translation_key' => 'nav.insights.retention', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Retention', 'value_fr' => 'Rétention', 'value_de' => 'Bindung', 'value_es' => 'Retención'],

            // Payments
            ['translation_key' => 'nav.payments', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Payments', 'value_fr' => 'Paiements', 'value_de' => 'Zahlungen', 'value_es' => 'Pagos'],
            ['translation_key' => 'nav.payments.transactions', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Transactions', 'value_fr' => 'Transactions', 'value_de' => 'Transaktionen', 'value_es' => 'Transacciones'],
            ['translation_key' => 'nav.payments.subscriptions', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Subscriptions', 'value_fr' => 'Abonnements', 'value_de' => 'Abonnements', 'value_es' => 'Suscripciones'],
            ['translation_key' => 'nav.payments.payouts', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Payouts', 'value_fr' => 'Versements', 'value_de' => 'Auszahlungen', 'value_es' => 'Pagos'],
            ['translation_key' => 'nav.payments.refunds', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Refunds', 'value_fr' => 'Remboursements', 'value_de' => 'Rückerstattungen', 'value_es' => 'Reembolsos'],

            // Settings & Sign Out
            ['translation_key' => 'nav.settings', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Settings', 'value_fr' => 'Paramètres', 'value_de' => 'Einstellungen', 'value_es' => 'Configuración'],
            ['translation_key' => 'nav.sign_out', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Sign Out', 'value_fr' => 'Déconnexion', 'value_de' => 'Abmelden', 'value_es' => 'Cerrar sesión'],

            // Badges
            ['translation_key' => 'nav.badge.soon', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Soon', 'value_fr' => 'Bientôt', 'value_de' => 'Bald', 'value_es' => 'Pronto'],
            ['translation_key' => 'nav.badge.later', 'category' => 'general_content', 'page_context' => 'sidebar',
             'value_en' => 'Later', 'value_fr' => 'Plus tard', 'value_de' => 'Später', 'value_es' => 'Después'],
        ];
    }

    /**
     * Button translations.
     */
    protected function getButtonTranslations(): array
    {
        return [
            ['translation_key' => 'btn.save', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Save', 'value_fr' => 'Enregistrer', 'value_de' => 'Speichern', 'value_es' => 'Guardar'],
            ['translation_key' => 'btn.save_changes', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Save Changes', 'value_fr' => 'Enregistrer les modifications', 'value_de' => 'Änderungen speichern', 'value_es' => 'Guardar cambios'],
            ['translation_key' => 'btn.cancel', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Cancel', 'value_fr' => 'Annuler', 'value_de' => 'Abbrechen', 'value_es' => 'Cancelar'],
            ['translation_key' => 'btn.close', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Close', 'value_fr' => 'Fermer', 'value_de' => 'Schließen', 'value_es' => 'Cerrar'],
            ['translation_key' => 'btn.delete', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Delete', 'value_fr' => 'Supprimer', 'value_de' => 'Löschen', 'value_es' => 'Eliminar'],
            ['translation_key' => 'btn.edit', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Edit', 'value_fr' => 'Modifier', 'value_de' => 'Bearbeiten', 'value_es' => 'Editar'],
            ['translation_key' => 'btn.add', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Add', 'value_fr' => 'Ajouter', 'value_de' => 'Hinzufügen', 'value_es' => 'Añadir'],
            ['translation_key' => 'btn.create', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Create', 'value_fr' => 'Créer', 'value_de' => 'Erstellen', 'value_es' => 'Crear'],
            ['translation_key' => 'btn.update', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Update', 'value_fr' => 'Mettre à jour', 'value_de' => 'Aktualisieren', 'value_es' => 'Actualizar'],
            ['translation_key' => 'btn.submit', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Submit', 'value_fr' => 'Soumettre', 'value_de' => 'Absenden', 'value_es' => 'Enviar'],
            ['translation_key' => 'btn.confirm', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Confirm', 'value_fr' => 'Confirmer', 'value_de' => 'Bestätigen', 'value_es' => 'Confirmar'],
            ['translation_key' => 'btn.back', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Back', 'value_fr' => 'Retour', 'value_de' => 'Zurück', 'value_es' => 'Atrás'],
            ['translation_key' => 'btn.next', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Next', 'value_fr' => 'Suivant', 'value_de' => 'Weiter', 'value_es' => 'Siguiente'],
            ['translation_key' => 'btn.previous', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Previous', 'value_fr' => 'Précédent', 'value_de' => 'Vorherige', 'value_es' => 'Anterior'],
            ['translation_key' => 'btn.search', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Search', 'value_fr' => 'Rechercher', 'value_de' => 'Suchen', 'value_es' => 'Buscar'],
            ['translation_key' => 'btn.filter', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Filter', 'value_fr' => 'Filtrer', 'value_de' => 'Filtern', 'value_es' => 'Filtrar'],
            ['translation_key' => 'btn.clear', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Clear', 'value_fr' => 'Effacer', 'value_de' => 'Löschen', 'value_es' => 'Borrar'],
            ['translation_key' => 'btn.reset', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Reset', 'value_fr' => 'Réinitialiser', 'value_de' => 'Zurücksetzen', 'value_es' => 'Restablecer'],
            ['translation_key' => 'btn.export', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Export', 'value_fr' => 'Exporter', 'value_de' => 'Exportieren', 'value_es' => 'Exportar'],
            ['translation_key' => 'btn.import', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Import', 'value_fr' => 'Importer', 'value_de' => 'Importieren', 'value_es' => 'Importar'],
            ['translation_key' => 'btn.view', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'View', 'value_fr' => 'Voir', 'value_de' => 'Ansehen', 'value_es' => 'Ver'],
            ['translation_key' => 'btn.view_all', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'View All', 'value_fr' => 'Voir tout', 'value_de' => 'Alle anzeigen', 'value_es' => 'Ver todo'],
            ['translation_key' => 'btn.view_details', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'View Details', 'value_fr' => 'Voir les détails', 'value_de' => 'Details anzeigen', 'value_es' => 'Ver detalles'],
            ['translation_key' => 'btn.book_now', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Book Now', 'value_fr' => 'Réserver maintenant', 'value_de' => 'Jetzt buchen', 'value_es' => 'Reservar ahora'],
            ['translation_key' => 'btn.book_class', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Book Class', 'value_fr' => 'Réserver un cours', 'value_de' => 'Kurs buchen', 'value_es' => 'Reservar clase'],
            ['translation_key' => 'btn.book_service', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Book Service', 'value_fr' => 'Réserver un service', 'value_de' => 'Service buchen', 'value_es' => 'Reservar servicio'],
            ['translation_key' => 'btn.join_waitlist', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Join Waitlist', 'value_fr' => 'Rejoindre la liste d\'attente', 'value_de' => 'Warteliste beitreten', 'value_es' => 'Unirse a la lista de espera'],
            ['translation_key' => 'btn.cancel_booking', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Cancel Booking', 'value_fr' => 'Annuler la réservation', 'value_de' => 'Buchung stornieren', 'value_es' => 'Cancelar reserva'],
            ['translation_key' => 'btn.publish', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Publish', 'value_fr' => 'Publier', 'value_de' => 'Veröffentlichen', 'value_es' => 'Publicar'],
            ['translation_key' => 'btn.unpublish', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Unpublish', 'value_fr' => 'Dépublier', 'value_de' => 'Veröffentlichung aufheben', 'value_es' => 'Despublicar'],
            ['translation_key' => 'btn.duplicate', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Duplicate', 'value_fr' => 'Dupliquer', 'value_de' => 'Duplizieren', 'value_es' => 'Duplicar'],
            ['translation_key' => 'btn.reschedule', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Reschedule', 'value_fr' => 'Reprogrammer', 'value_de' => 'Umbuchen', 'value_es' => 'Reprogramar'],
            ['translation_key' => 'btn.checkout', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Checkout', 'value_fr' => 'Paiement', 'value_de' => 'Zur Kasse', 'value_es' => 'Pagar'],
            ['translation_key' => 'btn.pay_now', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Pay Now', 'value_fr' => 'Payer maintenant', 'value_de' => 'Jetzt bezahlen', 'value_es' => 'Pagar ahora'],
            ['translation_key' => 'btn.sign_in', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Sign In', 'value_fr' => 'Se connecter', 'value_de' => 'Anmelden', 'value_es' => 'Iniciar sesión'],
            ['translation_key' => 'btn.sign_up', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Sign Up', 'value_fr' => 'S\'inscrire', 'value_de' => 'Registrieren', 'value_es' => 'Registrarse'],
            ['translation_key' => 'btn.sign_out', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Sign Out', 'value_fr' => 'Se déconnecter', 'value_de' => 'Abmelden', 'value_es' => 'Cerrar sesión'],
            ['translation_key' => 'btn.continue', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Continue', 'value_fr' => 'Continuer', 'value_de' => 'Fortfahren', 'value_es' => 'Continuar'],
            ['translation_key' => 'btn.request_booking', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Request Booking', 'value_fr' => 'Demander une réservation', 'value_de' => 'Buchung anfragen', 'value_es' => 'Solicitar reserva'],
            ['translation_key' => 'btn.member_login', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'Member Login', 'value_fr' => 'Connexion membre', 'value_de' => 'Mitglieder-Login', 'value_es' => 'Acceso miembros'],
            ['translation_key' => 'btn.my_portal', 'category' => 'buttons', 'page_context' => null,
             'value_en' => 'My Portal', 'value_fr' => 'Mon portail', 'value_de' => 'Mein Portal', 'value_es' => 'Mi portal'],
        ];
    }

    /**
     * Message translations.
     */
    protected function getMessageTranslations(): array
    {
        return [
            // Success messages
            ['translation_key' => 'msg.success.saved', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Changes saved successfully.', 'value_fr' => 'Modifications enregistrées avec succès.', 'value_de' => 'Änderungen erfolgreich gespeichert.', 'value_es' => 'Cambios guardados correctamente.'],
            ['translation_key' => 'msg.success.created', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Created successfully.', 'value_fr' => 'Créé avec succès.', 'value_de' => 'Erfolgreich erstellt.', 'value_es' => 'Creado correctamente.'],
            ['translation_key' => 'msg.success.updated', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Updated successfully.', 'value_fr' => 'Mis à jour avec succès.', 'value_de' => 'Erfolgreich aktualisiert.', 'value_es' => 'Actualizado correctamente.'],
            ['translation_key' => 'msg.success.deleted', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Deleted successfully.', 'value_fr' => 'Supprimé avec succès.', 'value_de' => 'Erfolgreich gelöscht.', 'value_es' => 'Eliminado correctamente.'],
            ['translation_key' => 'msg.success.booked', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Booking confirmed!', 'value_fr' => 'Réservation confirmée !', 'value_de' => 'Buchung bestätigt!', 'value_es' => '¡Reserva confirmada!'],
            ['translation_key' => 'msg.success.cancelled', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Booking cancelled successfully.', 'value_fr' => 'Réservation annulée avec succès.', 'value_de' => 'Buchung erfolgreich storniert.', 'value_es' => 'Reserva cancelada correctamente.'],

            // Error messages
            ['translation_key' => 'msg.error.general', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'An error occurred. Please try again.', 'value_fr' => 'Une erreur s\'est produite. Veuillez réessayer.', 'value_de' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.', 'value_es' => 'Se produjo un error. Por favor, inténtelo de nuevo.'],
            ['translation_key' => 'msg.error.not_found', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'The requested item was not found.', 'value_fr' => 'L\'élément demandé n\'a pas été trouvé.', 'value_de' => 'Das angeforderte Element wurde nicht gefunden.', 'value_es' => 'El elemento solicitado no fue encontrado.'],

            // Info messages
            ['translation_key' => 'msg.info.loading', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Loading...', 'value_fr' => 'Chargement...', 'value_de' => 'Laden...', 'value_es' => 'Cargando...'],
            ['translation_key' => 'msg.info.no_results', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'No results found.', 'value_fr' => 'Aucun résultat trouvé.', 'value_de' => 'Keine Ergebnisse gefunden.', 'value_es' => 'No se encontraron resultados.'],

            // Confirmation messages
            ['translation_key' => 'msg.confirm.delete', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Are you sure you want to delete this item?', 'value_fr' => 'Êtes-vous sûr de vouloir supprimer cet élément ?', 'value_de' => 'Sind Sie sicher, dass Sie dieses Element löschen möchten?', 'value_es' => '¿Está seguro de que desea eliminar este elemento?'],
            ['translation_key' => 'msg.confirm.cancel_booking', 'category' => 'messages', 'page_context' => null,
             'value_en' => 'Are you sure you want to cancel this booking?', 'value_fr' => 'Êtes-vous sûr de vouloir annuler cette réservation ?', 'value_de' => 'Sind Sie sicher, dass Sie diese Buchung stornieren möchten?', 'value_es' => '¿Está seguro de que desea cancelar esta reserva?'],
        ];
    }

    /**
     * Page title translations.
     */
    protected function getPageTitleTranslations(): array
    {
        return [
            ['translation_key' => 'page.dashboard', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Dashboard', 'value_fr' => 'Tableau de bord', 'value_de' => 'Dashboard', 'value_es' => 'Panel'],
            ['translation_key' => 'page.schedule', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Schedule', 'value_fr' => 'Emploi du temps', 'value_de' => 'Zeitplan', 'value_es' => 'Horario'],
            ['translation_key' => 'page.bookings', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Bookings', 'value_fr' => 'Réservations', 'value_de' => 'Buchungen', 'value_es' => 'Reservas'],
            ['translation_key' => 'page.clients', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Clients', 'value_fr' => 'Clients', 'value_de' => 'Kunden', 'value_es' => 'Clientes'],
            ['translation_key' => 'page.settings', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Settings', 'value_fr' => 'Paramètres', 'value_de' => 'Einstellungen', 'value_es' => 'Configuración'],
            ['translation_key' => 'page.profile', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Profile', 'value_fr' => 'Profil', 'value_de' => 'Profil', 'value_es' => 'Perfil'],
            ['translation_key' => 'page.checkout', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Checkout', 'value_fr' => 'Paiement', 'value_de' => 'Kasse', 'value_es' => 'Pago'],
            ['translation_key' => 'page.classes', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Classes', 'value_fr' => 'Cours', 'value_de' => 'Kurse', 'value_es' => 'Clases'],
            ['translation_key' => 'page.services', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Services', 'value_fr' => 'Services', 'value_de' => 'Dienstleistungen', 'value_es' => 'Servicios'],
            ['translation_key' => 'page.memberships', 'category' => 'page_titles', 'page_context' => null,
             'value_en' => 'Memberships', 'value_fr' => 'Abonnements', 'value_de' => 'Mitgliedschaften', 'value_es' => 'Membresías'],
        ];
    }

    /**
     * Field label translations.
     */
    protected function getFieldLabelTranslations(): array
    {
        return [
            // Personal info
            ['translation_key' => 'field.first_name', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'First Name', 'value_fr' => 'Prénom', 'value_de' => 'Vorname', 'value_es' => 'Nombre'],
            ['translation_key' => 'field.last_name', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Last Name', 'value_fr' => 'Nom', 'value_de' => 'Nachname', 'value_es' => 'Apellido'],
            ['translation_key' => 'field.email', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Email', 'value_fr' => 'E-mail', 'value_de' => 'E-Mail', 'value_es' => 'Correo electrónico'],
            ['translation_key' => 'field.phone', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Phone', 'value_fr' => 'Téléphone', 'value_de' => 'Telefon', 'value_es' => 'Teléfono'],
            ['translation_key' => 'field.date_of_birth', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Date of Birth', 'value_fr' => 'Date de naissance', 'value_de' => 'Geburtsdatum', 'value_es' => 'Fecha de nacimiento'],

            // Address
            ['translation_key' => 'field.address', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Address', 'value_fr' => 'Adresse', 'value_de' => 'Adresse', 'value_es' => 'Dirección'],
            ['translation_key' => 'field.city', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'City', 'value_fr' => 'Ville', 'value_de' => 'Stadt', 'value_es' => 'Ciudad'],
            ['translation_key' => 'field.country', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Country', 'value_fr' => 'Pays', 'value_de' => 'Land', 'value_es' => 'País'],

            // Booking
            ['translation_key' => 'field.date', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Date', 'value_fr' => 'Date', 'value_de' => 'Datum', 'value_es' => 'Fecha'],
            ['translation_key' => 'field.time', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Time', 'value_fr' => 'Heure', 'value_de' => 'Zeit', 'value_es' => 'Hora'],
            ['translation_key' => 'field.duration', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Duration', 'value_fr' => 'Durée', 'value_de' => 'Dauer', 'value_es' => 'Duración'],
            ['translation_key' => 'field.class', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Class', 'value_fr' => 'Cours', 'value_de' => 'Kurs', 'value_es' => 'Clase'],
            ['translation_key' => 'field.service', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Service', 'value_fr' => 'Service', 'value_de' => 'Dienstleistung', 'value_es' => 'Servicio'],
            ['translation_key' => 'field.client', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Client', 'value_fr' => 'Client', 'value_de' => 'Kunde', 'value_es' => 'Cliente'],
            ['translation_key' => 'field.start_date', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Start Date', 'value_fr' => 'Date de début', 'value_de' => 'Startdatum', 'value_es' => 'Fecha de inicio'],
            ['translation_key' => 'field.end_date', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'End Date', 'value_fr' => 'Date de fin', 'value_de' => 'Enddatum', 'value_es' => 'Fecha de fin'],
            ['translation_key' => 'field.days_of_week', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Days of Week', 'value_fr' => 'Jours de la semaine', 'value_de' => 'Wochentage', 'value_es' => 'Días de la semana'],
            ['translation_key' => 'field.select_service', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Select a service...', 'value_fr' => 'Sélectionner un service...', 'value_de' => 'Service auswählen...', 'value_es' => 'Seleccionar un servicio...'],
            ['translation_key' => 'field.select_instructor', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Select an instructor...', 'value_fr' => 'Sélectionner un instructeur...', 'value_de' => 'Trainer auswählen...', 'value_es' => 'Seleccionar un instructor...'],
            ['translation_key' => 'field.instructor', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Instructor', 'value_fr' => 'Instructeur', 'value_de' => 'Trainer', 'value_es' => 'Instructor'],
            ['translation_key' => 'field.location', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Location', 'value_fr' => 'Lieu', 'value_de' => 'Ort', 'value_es' => 'Ubicación'],
            ['translation_key' => 'field.class_plan', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Class Plan', 'value_fr' => 'Plan de cours', 'value_de' => 'Kursplan', 'value_es' => 'Plan de clase'],
            ['translation_key' => 'field.category', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Category', 'value_fr' => 'Catégorie', 'value_de' => 'Kategorie', 'value_es' => 'Categoría'],
            ['translation_key' => 'field.difficulty', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Difficulty', 'value_fr' => 'Difficulté', 'value_de' => 'Schwierigkeit', 'value_es' => 'Dificultad'],
            ['translation_key' => 'field.capacity', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Capacity', 'value_fr' => 'Capacité', 'value_de' => 'Kapazität', 'value_es' => 'Capacidad'],
            ['translation_key' => 'field.session_price', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Session Price', 'value_fr' => 'Prix de la session', 'value_de' => 'Sitzungspreis', 'value_es' => 'Precio de la sesión'],
            ['translation_key' => 'field.membership', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Membership', 'value_fr' => 'Adhésion', 'value_de' => 'Mitgliedschaft', 'value_es' => 'Membresía'],

            // Payment
            ['translation_key' => 'field.total', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Total', 'value_fr' => 'Total', 'value_de' => 'Gesamt', 'value_es' => 'Total'],
            ['translation_key' => 'field.subtotal', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Subtotal', 'value_fr' => 'Sous-total', 'value_de' => 'Zwischensumme', 'value_es' => 'Subtotal'],
            ['translation_key' => 'field.tax', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Tax', 'value_fr' => 'Taxe', 'value_de' => 'Steuer', 'value_es' => 'Impuesto'],
            ['translation_key' => 'field.discount', 'category' => 'field_labels', 'page_context' => null,
             'value_en' => 'Discount', 'value_fr' => 'Remise', 'value_de' => 'Rabatt', 'value_es' => 'Descuento'],
        ];
    }

    /**
     * Booking flow translations.
     */
    protected function getBookingTranslations(): array
    {
        return [
            // Booking page
            ['translation_key' => 'booking.select_class', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Select a Class', 'value_fr' => 'Sélectionner un cours', 'value_de' => 'Kurs auswählen', 'value_es' => 'Seleccionar una clase'],
            ['translation_key' => 'booking.select_service', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Select a Service', 'value_fr' => 'Sélectionner un service', 'value_de' => 'Service auswählen', 'value_es' => 'Seleccionar un servicio'],
            ['translation_key' => 'booking.select_date', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Select Date', 'value_fr' => 'Sélectionner la date', 'value_de' => 'Datum auswählen', 'value_es' => 'Seleccionar fecha'],
            ['translation_key' => 'booking.select_time', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Select Time', 'value_fr' => 'Sélectionner l\'heure', 'value_de' => 'Zeit auswählen', 'value_es' => 'Seleccionar hora'],
            ['translation_key' => 'booking.available_spots', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => ':count spots available', 'value_fr' => ':count places disponibles', 'value_de' => ':count Plätze verfügbar', 'value_es' => ':count plazas disponibles'],
            ['translation_key' => 'booking.class_full', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Class Full', 'value_fr' => 'Cours complet', 'value_de' => 'Kurs voll', 'value_es' => 'Clase llena'],
            ['translation_key' => 'booking.no_classes_available', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'No classes available for this date.', 'value_fr' => 'Aucun cours disponible pour cette date.', 'value_de' => 'Keine Kurse für dieses Datum verfügbar.', 'value_es' => 'No hay clases disponibles para esta fecha.'],

            // Checkout
            ['translation_key' => 'booking.order_summary', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Order Summary', 'value_fr' => 'Récapitulatif de la commande', 'value_de' => 'Bestellübersicht', 'value_es' => 'Resumen del pedido'],
            ['translation_key' => 'booking.payment_info', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Payment Information', 'value_fr' => 'Informations de paiement', 'value_de' => 'Zahlungsinformationen', 'value_es' => 'Información de pago'],

            // Confirmation
            ['translation_key' => 'booking.confirmed_title', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Booking Confirmed!', 'value_fr' => 'Réservation confirmée !', 'value_de' => 'Buchung bestätigt!', 'value_es' => '¡Reserva confirmada!'],
            ['translation_key' => 'booking.confirmed_message', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Your booking has been confirmed. A confirmation email has been sent to your email address.',
             'value_fr' => 'Votre réservation a été confirmée. Un email de confirmation a été envoyé à votre adresse email.',
             'value_de' => 'Ihre Buchung wurde bestätigt. Eine Bestätigungs-E-Mail wurde an Ihre E-Mail-Adresse gesendet.',
             'value_es' => 'Su reserva ha sido confirmada. Se ha enviado un correo de confirmación a su dirección de correo electrónico.'],
            ['translation_key' => 'booking.add_to_calendar', 'category' => 'general_content', 'page_context' => 'booking',
             'value_en' => 'Add to Calendar', 'value_fr' => 'Ajouter au calendrier', 'value_de' => 'Zum Kalender hinzufügen', 'value_es' => 'Añadir al calendario'],

            // Subdomain navbar
            ['translation_key' => 'subdomain.select_language', 'category' => 'general_content', 'page_context' => 'subdomain',
             'value_en' => 'Select Language', 'value_fr' => 'Sélectionner la langue', 'value_de' => 'Sprache auswählen', 'value_es' => 'Seleccionar idioma'],
            ['translation_key' => 'subdomain.select_currency', 'category' => 'general_content', 'page_context' => 'subdomain',
             'value_en' => 'Select Currency', 'value_fr' => 'Sélectionner la devise', 'value_de' => 'Währung auswählen', 'value_es' => 'Seleccionar moneda'],
        ];
    }

    /**
     * Dashboard page translations.
     */
    protected function getDashboardTranslations(): array
    {
        return [
            // Dashboard widgets
            ['translation_key' => 'dashboard.welcome', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Welcome back', 'value_fr' => 'Bon retour', 'value_de' => 'Willkommen zurück', 'value_es' => 'Bienvenido de nuevo'],
            ['translation_key' => 'dashboard.todays_summary', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => "Today's Summary", 'value_fr' => "Résumé d'aujourd'hui", 'value_de' => 'Heutige Zusammenfassung', 'value_es' => 'Resumen de hoy'],
            ['translation_key' => 'dashboard.total_bookings', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Total Bookings', 'value_fr' => 'Réservations totales', 'value_de' => 'Gesamtbuchungen', 'value_es' => 'Reservas totales'],
            ['translation_key' => 'dashboard.active_members', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Active Members', 'value_fr' => 'Membres actifs', 'value_de' => 'Aktive Mitglieder', 'value_es' => 'Miembros activos'],
            ['translation_key' => 'dashboard.revenue_today', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => "Today's Revenue", 'value_fr' => "Revenus d'aujourd'hui", 'value_de' => 'Heutige Einnahmen', 'value_es' => 'Ingresos de hoy'],
            ['translation_key' => 'dashboard.classes_today', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Classes Today', 'value_fr' => "Cours aujourd'hui", 'value_de' => 'Kurse heute', 'value_es' => 'Clases hoy'],
            ['translation_key' => 'dashboard.upcoming_classes', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Upcoming Classes', 'value_fr' => 'Cours à venir', 'value_de' => 'Kommende Kurse', 'value_es' => 'Próximas clases'],
            ['translation_key' => 'dashboard.recent_activity', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Recent Activity', 'value_fr' => 'Activité récente', 'value_de' => 'Letzte Aktivität', 'value_es' => 'Actividad reciente'],
            ['translation_key' => 'dashboard.quick_actions', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Quick Actions', 'value_fr' => 'Actions rapides', 'value_de' => 'Schnellaktionen', 'value_es' => 'Acciones rápidas'],
            ['translation_key' => 'dashboard.new_booking', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'New Booking', 'value_fr' => 'Nouvelle réservation', 'value_de' => 'Neue Buchung', 'value_es' => 'Nueva reserva'],
            ['translation_key' => 'dashboard.add_client', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Add Client', 'value_fr' => 'Ajouter un client', 'value_de' => 'Kunde hinzufügen', 'value_es' => 'Añadir cliente'],
            ['translation_key' => 'dashboard.create_class', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Create Class', 'value_fr' => 'Créer un cours', 'value_de' => 'Kurs erstellen', 'value_es' => 'Crear clase'],
            ['translation_key' => 'dashboard.attendance_rate', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Attendance Rate', 'value_fr' => 'Taux de présence', 'value_de' => 'Anwesenheitsrate', 'value_es' => 'Tasa de asistencia'],
            ['translation_key' => 'dashboard.pending_requests', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'Pending Requests', 'value_fr' => 'Demandes en attente', 'value_de' => 'Ausstehende Anfragen', 'value_es' => 'Solicitudes pendientes'],
            ['translation_key' => 'dashboard.no_classes_today', 'category' => 'general_content', 'page_context' => 'dashboard',
             'value_en' => 'No classes scheduled for today', 'value_fr' => 'Aucun cours prévu pour aujourd\'hui', 'value_de' => 'Keine Kurse für heute geplant', 'value_es' => 'No hay clases programadas para hoy'],
        ];
    }

    /**
     * Clients page translations.
     */
    protected function getClientsTranslations(): array
    {
        return [
            // Client list
            ['translation_key' => 'clients.title', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Clients', 'value_fr' => 'Clients', 'value_de' => 'Kunden', 'value_es' => 'Clientes'],
            ['translation_key' => 'clients.all_clients', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'All Clients', 'value_fr' => 'Tous les clients', 'value_de' => 'Alle Kunden', 'value_es' => 'Todos los clientes'],
            ['translation_key' => 'clients.add_client', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Add Client', 'value_fr' => 'Ajouter un client', 'value_de' => 'Kunde hinzufügen', 'value_es' => 'Añadir cliente'],
            ['translation_key' => 'clients.edit_client', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Edit Client', 'value_fr' => 'Modifier le client', 'value_de' => 'Kunde bearbeiten', 'value_es' => 'Editar cliente'],
            ['translation_key' => 'clients.client_details', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Client Details', 'value_fr' => 'Détails du client', 'value_de' => 'Kundendetails', 'value_es' => 'Detalles del cliente'],
            ['translation_key' => 'clients.contact_info', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Contact Information', 'value_fr' => 'Informations de contact', 'value_de' => 'Kontaktinformationen', 'value_es' => 'Información de contacto'],
            ['translation_key' => 'clients.booking_history', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Booking History', 'value_fr' => 'Historique des réservations', 'value_de' => 'Buchungsverlauf', 'value_es' => 'Historial de reservas'],
            ['translation_key' => 'clients.membership_status', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Membership Status', 'value_fr' => 'Statut d\'adhésion', 'value_de' => 'Mitgliedschaftsstatus', 'value_es' => 'Estado de membresía'],
            ['translation_key' => 'clients.active', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Active', 'value_fr' => 'Actif', 'value_de' => 'Aktiv', 'value_es' => 'Activo'],
            ['translation_key' => 'clients.inactive', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Inactive', 'value_fr' => 'Inactif', 'value_de' => 'Inaktiv', 'value_es' => 'Inactivo'],
            ['translation_key' => 'clients.total_spent', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Total Spent', 'value_fr' => 'Total dépensé', 'value_de' => 'Gesamtausgaben', 'value_es' => 'Total gastado'],
            ['translation_key' => 'clients.last_visit', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Last Visit', 'value_fr' => 'Dernière visite', 'value_de' => 'Letzter Besuch', 'value_es' => 'Última visita'],
            ['translation_key' => 'clients.joined_date', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Joined', 'value_fr' => 'Inscrit le', 'value_de' => 'Beigetreten', 'value_es' => 'Se unió'],
            ['translation_key' => 'clients.notes', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Notes', 'value_fr' => 'Notes', 'value_de' => 'Notizen', 'value_es' => 'Notas'],
            ['translation_key' => 'clients.add_note', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Add Note', 'value_fr' => 'Ajouter une note', 'value_de' => 'Notiz hinzufügen', 'value_es' => 'Añadir nota'],
            ['translation_key' => 'clients.send_message', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Send Message', 'value_fr' => 'Envoyer un message', 'value_de' => 'Nachricht senden', 'value_es' => 'Enviar mensaje'],
            ['translation_key' => 'clients.search_placeholder', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Search clients...', 'value_fr' => 'Rechercher des clients...', 'value_de' => 'Kunden suchen...', 'value_es' => 'Buscar clientes...'],
            ['translation_key' => 'clients.no_clients', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'No clients found', 'value_fr' => 'Aucun client trouvé', 'value_de' => 'Keine Kunden gefunden', 'value_es' => 'No se encontraron clientes'],

            // Leads
            ['translation_key' => 'clients.leads', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Leads', 'value_fr' => 'Prospects', 'value_de' => 'Leads', 'value_es' => 'Prospectos'],
            ['translation_key' => 'clients.convert_to_member', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Convert to Member', 'value_fr' => 'Convertir en membre', 'value_de' => 'Zum Mitglied konvertieren', 'value_es' => 'Convertir a miembro'],
            ['translation_key' => 'clients.lead_source', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Lead Source', 'value_fr' => 'Source du prospect', 'value_de' => 'Lead-Quelle', 'value_es' => 'Fuente del prospecto'],

            // At-Risk
            ['translation_key' => 'clients.at_risk', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'At-Risk Clients', 'value_fr' => 'Clients à risque', 'value_de' => 'Gefährdete Kunden', 'value_es' => 'Clientes en riesgo'],
            ['translation_key' => 'clients.days_since_last_visit', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Days since last visit', 'value_fr' => 'Jours depuis la dernière visite', 'value_de' => 'Tage seit dem letzten Besuch', 'value_es' => 'Días desde la última visita'],

            // Tags
            ['translation_key' => 'clients.tags', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Tags', 'value_fr' => 'Étiquettes', 'value_de' => 'Tags', 'value_es' => 'Etiquetas'],
            ['translation_key' => 'clients.add_tag', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Add Tag', 'value_fr' => 'Ajouter une étiquette', 'value_de' => 'Tag hinzufügen', 'value_es' => 'Añadir etiqueta'],
            ['translation_key' => 'clients.manage_tags', 'category' => 'general_content', 'page_context' => 'clients',
             'value_en' => 'Manage Tags', 'value_fr' => 'Gérer les étiquettes', 'value_de' => 'Tags verwalten', 'value_es' => 'Gestionar etiquetas'],
        ];
    }

    /**
     * Schedule page translations.
     */
    protected function getScheduleTranslations(): array
    {
        return [
            // Calendar
            ['translation_key' => 'schedule.calendar', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Calendar', 'value_fr' => 'Calendrier', 'value_de' => 'Kalender', 'value_es' => 'Calendario'],
            ['translation_key' => 'schedule.day_view', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Day', 'value_fr' => 'Jour', 'value_de' => 'Tag', 'value_es' => 'Día'],
            ['translation_key' => 'schedule.week_view', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Week', 'value_fr' => 'Semaine', 'value_de' => 'Woche', 'value_es' => 'Semana'],
            ['translation_key' => 'schedule.month_view', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Month', 'value_fr' => 'Mois', 'value_de' => 'Monat', 'value_es' => 'Mes'],
            ['translation_key' => 'schedule.today', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Today', 'value_fr' => 'Aujourd\'hui', 'value_de' => 'Heute', 'value_es' => 'Hoy'],
            ['translation_key' => 'schedule.tomorrow', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Tomorrow', 'value_fr' => 'Demain', 'value_de' => 'Morgen', 'value_es' => 'Mañana'],

            // Class sessions
            ['translation_key' => 'schedule.class_sessions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Class Sessions', 'value_fr' => 'Sessions de cours', 'value_de' => 'Kurseinheiten', 'value_es' => 'Sesiones de clase'],
            ['translation_key' => 'schedule.add_class', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Add Class', 'value_fr' => 'Ajouter un cours', 'value_de' => 'Kurs hinzufügen', 'value_es' => 'Añadir clase'],
            ['translation_key' => 'schedule.edit_class', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Edit Class', 'value_fr' => 'Modifier le cours', 'value_de' => 'Kurs bearbeiten', 'value_es' => 'Editar clase'],
            ['translation_key' => 'schedule.class_details', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Class Details', 'value_fr' => 'Détails du cours', 'value_de' => 'Kursdetails', 'value_es' => 'Detalles de la clase'],
            ['translation_key' => 'schedule.cancel_class', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Cancel Class', 'value_fr' => 'Annuler le cours', 'value_de' => 'Kurs stornieren', 'value_es' => 'Cancelar clase'],

            // Service slots
            ['translation_key' => 'schedule.service_slots', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Service Slots', 'value_fr' => 'Créneaux de service', 'value_de' => 'Service-Slots', 'value_es' => 'Horarios de servicio'],
            ['translation_key' => 'schedule.add_slot', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Add Slot', 'value_fr' => 'Ajouter un créneau', 'value_de' => 'Slot hinzufügen', 'value_es' => 'Añadir horario'],

            // Common schedule terms
            ['translation_key' => 'schedule.start_time', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Start Time', 'value_fr' => 'Heure de début', 'value_de' => 'Startzeit', 'value_es' => 'Hora de inicio'],
            ['translation_key' => 'schedule.end_time', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'End Time', 'value_fr' => 'Heure de fin', 'value_de' => 'Endzeit', 'value_es' => 'Hora de fin'],
            ['translation_key' => 'schedule.duration', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Duration', 'value_fr' => 'Durée', 'value_de' => 'Dauer', 'value_es' => 'Duración'],
            ['translation_key' => 'schedule.capacity', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Capacity', 'value_fr' => 'Capacité', 'value_de' => 'Kapazität', 'value_es' => 'Capacidad'],
            ['translation_key' => 'schedule.booked', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Booked', 'value_fr' => 'Réservé', 'value_de' => 'Gebucht', 'value_es' => 'Reservado'],
            ['translation_key' => 'schedule.available', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Available', 'value_fr' => 'Disponible', 'value_de' => 'Verfügbar', 'value_es' => 'Disponible'],
            ['translation_key' => 'schedule.waitlist', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Waitlist', 'value_fr' => 'Liste d\'attente', 'value_de' => 'Warteliste', 'value_es' => 'Lista de espera'],
            ['translation_key' => 'schedule.recurring', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Recurring', 'value_fr' => 'Récurrent', 'value_de' => 'Wiederkehrend', 'value_es' => 'Recurrente'],
            ['translation_key' => 'schedule.one_time', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'One-time', 'value_fr' => 'Unique', 'value_de' => 'Einmalig', 'value_es' => 'Única vez'],
            ['translation_key' => 'schedule.no_sessions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No sessions scheduled', 'value_fr' => 'Aucune session prévue', 'value_de' => 'Keine Sitzungen geplant', 'value_es' => 'No hay sesiones programadas'],

            // Days of week
            ['translation_key' => 'schedule.monday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Monday', 'value_fr' => 'Lundi', 'value_de' => 'Montag', 'value_es' => 'Lunes'],
            ['translation_key' => 'schedule.tuesday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Tuesday', 'value_fr' => 'Mardi', 'value_de' => 'Dienstag', 'value_es' => 'Martes'],
            ['translation_key' => 'schedule.wednesday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Wednesday', 'value_fr' => 'Mercredi', 'value_de' => 'Mittwoch', 'value_es' => 'Miércoles'],
            ['translation_key' => 'schedule.thursday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Thursday', 'value_fr' => 'Jeudi', 'value_de' => 'Donnerstag', 'value_es' => 'Jueves'],
            ['translation_key' => 'schedule.friday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Friday', 'value_fr' => 'Vendredi', 'value_de' => 'Freitag', 'value_es' => 'Viernes'],
            ['translation_key' => 'schedule.saturday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Saturday', 'value_fr' => 'Samedi', 'value_de' => 'Samstag', 'value_es' => 'Sábado'],
            ['translation_key' => 'schedule.sunday', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Sunday', 'value_fr' => 'Dimanche', 'value_de' => 'Sonntag', 'value_es' => 'Domingo'],

            // Class sessions page
            ['translation_key' => 'schedule.schedule_class', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Schedule Class', 'value_fr' => 'Planifier un cours', 'value_de' => 'Kurs planen', 'value_es' => 'Programar clase'],
            ['translation_key' => 'schedule.all_scheduled_classes', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All scheduled classes', 'value_fr' => 'Tous les cours programmés', 'value_de' => 'Alle geplanten Kurse', 'value_es' => 'Todas las clases programadas'],
            ['translation_key' => 'schedule.scheduling_conflict', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Scheduling Conflict', 'value_fr' => 'Conflit de planification', 'value_de' => 'Terminkonflikt', 'value_es' => 'Conflicto de horario'],
            ['translation_key' => 'schedule.conflicts_need_resolution', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Some sessions have scheduling conflicts that need to be resolved.', 'value_fr' => 'Certaines sessions ont des conflits de planification à résoudre.', 'value_de' => 'Einige Sitzungen haben Terminkon­flikte, die gelöst werden müssen.', 'value_es' => 'Algunas sesiones tienen conflictos de horario que deben resolverse.'],
            ['translation_key' => 'schedule.view_conflicts', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'View Conflicts', 'value_fr' => 'Voir les conflits', 'value_de' => 'Konflikte anzeigen', 'value_es' => 'Ver conflictos'],
            ['translation_key' => 'schedule.range', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Range', 'value_fr' => 'Période', 'value_de' => 'Zeitraum', 'value_es' => 'Rango'],
            ['translation_key' => 'schedule.week', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Week', 'value_fr' => 'Semaine', 'value_de' => 'Woche', 'value_es' => 'Semana'],
            ['translation_key' => 'schedule.month', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Month', 'value_fr' => 'Mois', 'value_de' => 'Monat', 'value_es' => 'Mes'],
            ['translation_key' => 'schedule.all_instructors', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All Instructors', 'value_fr' => 'Tous les instructeurs', 'value_de' => 'Alle Trainer', 'value_es' => 'Todos los instructores'],
            ['translation_key' => 'schedule.all_statuses', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All Statuses', 'value_fr' => 'Tous les statuts', 'value_de' => 'Alle Status', 'value_es' => 'Todos los estados'],
            ['translation_key' => 'schedule.conflicts_only', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Conflicts Only', 'value_fr' => 'Conflits uniquement', 'value_de' => 'Nur Konflikte', 'value_es' => 'Solo conflictos'],

            // Navigation
            ['translation_key' => 'schedule.previous_day', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Previous Day', 'value_fr' => 'Jour précédent', 'value_de' => 'Vorheriger Tag', 'value_es' => 'Día anterior'],
            ['translation_key' => 'schedule.previous_week', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Previous Week', 'value_fr' => 'Semaine précédente', 'value_de' => 'Vorherige Woche', 'value_es' => 'Semana anterior'],
            ['translation_key' => 'schedule.previous_month', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Previous Month', 'value_fr' => 'Mois précédent', 'value_de' => 'Vorheriger Monat', 'value_es' => 'Mes anterior'],
            ['translation_key' => 'schedule.next_day', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Next Day', 'value_fr' => 'Jour suivant', 'value_de' => 'Nächster Tag', 'value_es' => 'Día siguiente'],
            ['translation_key' => 'schedule.next_week', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Next Week', 'value_fr' => 'Semaine suivante', 'value_de' => 'Nächste Woche', 'value_es' => 'Semana siguiente'],
            ['translation_key' => 'schedule.next_month', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Next Month', 'value_fr' => 'Mois suivant', 'value_de' => 'Nächster Monat', 'value_es' => 'Mes siguiente'],

            // Empty states
            ['translation_key' => 'schedule.no_sessions_found', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No Sessions Found', 'value_fr' => 'Aucune session trouvée', 'value_de' => 'Keine Sitzungen gefunden', 'value_es' => 'No se encontraron sesiones'],
            ['translation_key' => 'schedule.no_sessions_filter', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No class sessions match your current filters.', 'value_fr' => 'Aucune session ne correspond à vos filtres.', 'value_de' => 'Keine Kurse entsprechen Ihren Filtern.', 'value_es' => 'Ninguna sesión coincide con tus filtros.'],
            ['translation_key' => 'schedule.no_sessions_today', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No class sessions scheduled for today.', 'value_fr' => 'Aucun cours prévu pour aujourd\'hui.', 'value_de' => 'Keine Kurse für heute geplant.', 'value_es' => 'No hay clases programadas para hoy.'],
            ['translation_key' => 'schedule.no_sessions_week', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No class sessions scheduled for this week.', 'value_fr' => 'Aucun cours prévu pour cette semaine.', 'value_de' => 'Keine Kurse für diese Woche geplant.', 'value_es' => 'No hay clases programadas para esta semana.'],
            ['translation_key' => 'schedule.no_sessions_month', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No class sessions scheduled for this month.', 'value_fr' => 'Aucun cours prévu pour ce mois.', 'value_de' => 'Keine Kurse für diesen Monat geplant.', 'value_es' => 'No hay clases programadas para este mes.'],
            ['translation_key' => 'schedule.schedule_first_class', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Schedule Your First Class', 'value_fr' => 'Planifiez votre premier cours', 'value_de' => 'Planen Sie Ihren ersten Kurs', 'value_es' => 'Programa tu primera clase'],

            // Table columns & labels
            ['translation_key' => 'schedule.check_ins', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Check-ins', 'value_fr' => 'Enregistrements', 'value_de' => 'Check-ins', 'value_es' => 'Registros'],
            ['translation_key' => 'schedule.intake', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Intake', 'value_fr' => 'Admission', 'value_de' => 'Aufnahme', 'value_es' => 'Admisión'],
            ['translation_key' => 'schedule.conflict', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Conflict', 'value_fr' => 'Conflit', 'value_de' => 'Konflikt', 'value_es' => 'Conflicto'],
            ['translation_key' => 'schedule.add_booking', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Add Booking', 'value_fr' => 'Ajouter une réservation', 'value_de' => 'Buchung hinzufügen', 'value_es' => 'Añadir reserva'],
            ['translation_key' => 'schedule.resolve_conflict', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Resolve Conflict', 'value_fr' => 'Résoudre le conflit', 'value_de' => 'Konflikt lösen', 'value_es' => 'Resolver conflicto'],
            ['translation_key' => 'schedule.confirm_cancel_session', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Cancel this session?', 'value_fr' => 'Annuler cette session ?', 'value_de' => 'Diese Sitzung stornieren?', 'value_es' => '¿Cancelar esta sesión?'],
            ['translation_key' => 'schedule.confirm_delete_session', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Delete this session?', 'value_fr' => 'Supprimer cette session ?', 'value_de' => 'Diese Sitzung löschen?', 'value_es' => '¿Eliminar esta sesión?'],

            // Service slots page
            ['translation_key' => 'schedule.all_upcoming_slots', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All upcoming slots', 'value_fr' => 'Tous les créneaux à venir', 'value_de' => 'Alle kommenden Slots', 'value_es' => 'Todos los horarios próximos'],
            ['translation_key' => 'schedule.bulk_add', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Bulk Add', 'value_fr' => 'Ajout en masse', 'value_de' => 'Massenhinzufügen', 'value_es' => 'Añadir en masa'],
            ['translation_key' => 'schedule.all_services', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All Services', 'value_fr' => 'Tous les services', 'value_de' => 'Alle Dienstleistungen', 'value_es' => 'Todos los servicios'],
            ['translation_key' => 'schedule.no_slots_found', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No Slots Found', 'value_fr' => 'Aucun créneau trouvé', 'value_de' => 'Keine Slots gefunden', 'value_es' => 'No se encontraron horarios'],
            ['translation_key' => 'schedule.no_slots_filter', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No service slots match your current filters.', 'value_fr' => 'Aucun créneau ne correspond à vos filtres.', 'value_de' => 'Keine Slots entsprechen Ihren Filtern.', 'value_es' => 'Ningún horario coincide con tus filtros.'],
            ['translation_key' => 'schedule.no_slots_today', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No service slots scheduled for today.', 'value_fr' => 'Aucun créneau prévu pour aujourd\'hui.', 'value_de' => 'Keine Slots für heute geplant.', 'value_es' => 'No hay horarios programados para hoy.'],
            ['translation_key' => 'schedule.no_slots_week', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No service slots scheduled for this week.', 'value_fr' => 'Aucun créneau prévu pour cette semaine.', 'value_de' => 'Keine Slots für diese Woche geplant.', 'value_es' => 'No hay horarios programados para esta semana.'],
            ['translation_key' => 'schedule.no_slots_month', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No service slots scheduled for this month.', 'value_fr' => 'Aucun créneau prévu pour ce mois.', 'value_de' => 'Keine Slots für diesen Monat geplant.', 'value_es' => 'No hay horarios programados para este mes.'],
            ['translation_key' => 'schedule.no_slots_upcoming', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No upcoming service slots found.', 'value_fr' => 'Aucun créneau à venir trouvé.', 'value_de' => 'Keine kommenden Slots gefunden.', 'value_es' => 'No se encontraron horarios próximos.'],
            ['translation_key' => 'schedule.add_first_slot', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Add Your First Slot', 'value_fr' => 'Ajoutez votre premier créneau', 'value_de' => 'Fügen Sie Ihren ersten Slot hinzu', 'value_es' => 'Añade tu primer horario'],
            ['translation_key' => 'schedule.confirm_delete_slot', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Delete this slot?', 'value_fr' => 'Supprimer ce créneau ?', 'value_de' => 'Diesen Slot löschen?', 'value_es' => '¿Eliminar este horario?'],
            ['translation_key' => 'schedule.bulk_add_slots', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Bulk Add Service Slots', 'value_fr' => 'Ajout en masse de créneaux', 'value_de' => 'Service-Slots in Masse hinzufügen', 'value_es' => 'Añadir horarios en masa'],
            ['translation_key' => 'schedule.time_slots', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Time Slots', 'value_fr' => 'Créneaux horaires', 'value_de' => 'Zeitfenster', 'value_es' => 'Horarios'],
            ['translation_key' => 'schedule.time_slots_help', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Add multiple times to create slots at each time on selected days.', 'value_fr' => 'Ajoutez plusieurs heures pour créer des créneaux à chaque heure les jours sélectionnés.', 'value_de' => 'Fügen Sie mehrere Zeiten hinzu, um Slots zu jeder Zeit an ausgewählten Tagen zu erstellen.', 'value_es' => 'Añade múltiples horarios para crear espacios en cada momento de los días seleccionados.'],
            ['translation_key' => 'schedule.create_slots', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Create Slots', 'value_fr' => 'Créer les créneaux', 'value_de' => 'Slots erstellen', 'value_es' => 'Crear horarios'],

            // Membership sessions page
            ['translation_key' => 'schedule.membership_sessions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Membership Sessions', 'value_fr' => 'Sessions d\'adhésion', 'value_de' => 'Mitgliedschaftssitzungen', 'value_es' => 'Sesiones de membresía'],
            ['translation_key' => 'schedule.membership_session', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Membership Session', 'value_fr' => 'Session d\'adhésion', 'value_de' => 'Mitgliedschaftssitzung', 'value_es' => 'Sesión de membresía'],
            ['translation_key' => 'schedule.all_upcoming_sessions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All upcoming sessions', 'value_fr' => 'Toutes les sessions à venir', 'value_de' => 'Alle kommenden Sitzungen', 'value_es' => 'Todas las sesiones próximas'],
            ['translation_key' => 'schedule.all_memberships', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'All Memberships', 'value_fr' => 'Toutes les adhésions', 'value_de' => 'Alle Mitgliedschaften', 'value_es' => 'Todas las membresías'],
            ['translation_key' => 'schedule.no_membership_sessions_filter', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No membership sessions match your current filters.', 'value_fr' => 'Aucune session d\'adhésion ne correspond à vos filtres.', 'value_de' => 'Keine Mitgliedschaftssitzungen entsprechen Ihren Filtern.', 'value_es' => 'Ninguna sesión de membresía coincide con tus filtros.'],
            ['translation_key' => 'schedule.no_membership_sessions_today', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No membership sessions scheduled for today.', 'value_fr' => 'Aucune session d\'adhésion prévue pour aujourd\'hui.', 'value_de' => 'Keine Mitgliedschaftssitzungen für heute geplant.', 'value_es' => 'No hay sesiones de membresía programadas para hoy.'],
            ['translation_key' => 'schedule.no_membership_sessions_week', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No membership sessions scheduled for this week.', 'value_fr' => 'Aucune session d\'adhésion prévue pour cette semaine.', 'value_de' => 'Keine Mitgliedschaftssitzungen für diese Woche geplant.', 'value_es' => 'No hay sesiones de membresía programadas para esta semana.'],
            ['translation_key' => 'schedule.no_membership_sessions_month', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No membership sessions scheduled for this month.', 'value_fr' => 'Aucune session d\'adhésion prévue pour ce mois.', 'value_de' => 'Keine Mitgliedschaftssitzungen für diesen Monat geplant.', 'value_es' => 'No hay sesiones de membresía programadas para este mes.'],
            ['translation_key' => 'schedule.no_membership_sessions_upcoming', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No upcoming membership sessions found.', 'value_fr' => 'Aucune session d\'adhésion à venir trouvée.', 'value_de' => 'Keine kommenden Mitgliedschaftssitzungen gefunden.', 'value_es' => 'No se encontraron sesiones de membresía próximas.'],
            ['translation_key' => 'schedule.schedule_first_session', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Schedule First Session', 'value_fr' => 'Planifier la première session', 'value_de' => 'Erste Sitzung planen', 'value_es' => 'Programar primera sesión'],
            ['translation_key' => 'schedule.delete_session_confirm', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Delete this session?', 'value_fr' => 'Supprimer cette session ?', 'value_de' => 'Diese Sitzung löschen?', 'value_es' => '¿Eliminar esta sesión?'],

            // Class session show page
            ['translation_key' => 'schedule.has_scheduling_conflict', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Has Scheduling Conflict', 'value_fr' => 'A un conflit d\'horaire', 'value_de' => 'Hat Terminkonflikt', 'value_es' => 'Tiene conflicto de horario'],
            ['translation_key' => 'schedule.conflict_needs_resolved', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'This session has a scheduling conflict that needs to be resolved.', 'value_fr' => 'Cette session a un conflit d\'horaire qui doit être résolu.', 'value_de' => 'Diese Sitzung hat einen Terminkonflikt, der gelöst werden muss.', 'value_es' => 'Esta sesión tiene un conflicto de horario que debe resolverse.'],
            ['translation_key' => 'schedule.mark_as_resolved', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Mark as Resolved', 'value_fr' => 'Marquer comme résolu', 'value_de' => 'Als gelöst markieren', 'value_es' => 'Marcar como resuelto'],
            ['translation_key' => 'schedule.conflict_resolved', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Conflict Resolved', 'value_fr' => 'Conflit résolu', 'value_de' => 'Konflikt gelöst', 'value_es' => 'Conflicto resuelto'],
            ['translation_key' => 'schedule.resolved_on', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Resolved on', 'value_fr' => 'Résolu le', 'value_de' => 'Gelöst am', 'value_es' => 'Resuelto el'],
            ['translation_key' => 'schedule.promote_backup', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Promote Backup', 'value_fr' => 'Promouvoir le remplaçant', 'value_de' => 'Ersatz befördern', 'value_es' => 'Promover suplente'],
            ['translation_key' => 'schedule.cancel_session', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Cancel Session', 'value_fr' => 'Annuler la session', 'value_de' => 'Sitzung stornieren', 'value_es' => 'Cancelar sesión'],
            ['translation_key' => 'schedule.session_details', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Session Details', 'value_fr' => 'Détails de la session', 'value_de' => 'Sitzungsdetails', 'value_es' => 'Detalles de la sesión'],
            ['translation_key' => 'schedule.recurrence', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Recurrence', 'value_fr' => 'Récurrence', 'value_de' => 'Wiederholung', 'value_es' => 'Recurrencia'],
            ['translation_key' => 'schedule.recurring', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Recurring', 'value_fr' => 'Récurrent', 'value_de' => 'Wiederkehrend', 'value_es' => 'Recurrente'],
            ['translation_key' => 'schedule.sessions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Sessions', 'value_fr' => 'Sessions', 'value_de' => 'Sitzungen', 'value_es' => 'Sesiones'],
            ['translation_key' => 'schedule.ends', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Ends', 'value_fr' => 'Se termine', 'value_de' => 'Endet', 'value_es' => 'Termina'],
            ['translation_key' => 'schedule.days', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Days', 'value_fr' => 'Jours', 'value_de' => 'Tage', 'value_es' => 'Días'],
            ['translation_key' => 'schedule.parent', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Parent', 'value_fr' => 'Parent', 'value_de' => 'Übergeordnet', 'value_es' => 'Padre'],
            ['translation_key' => 'schedule.view_series', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'View series', 'value_fr' => 'Voir la série', 'value_de' => 'Serie anzeigen', 'value_es' => 'Ver serie'],
            ['translation_key' => 'schedule.cancelled_on', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Cancelled on', 'value_fr' => 'Annulé le', 'value_de' => 'Storniert am', 'value_es' => 'Cancelado el'],
            ['translation_key' => 'schedule.primary', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Primary', 'value_fr' => 'Principal', 'value_de' => 'Haupt', 'value_es' => 'Principal'],
            ['translation_key' => 'schedule.backup', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Backup', 'value_fr' => 'Remplaçant', 'value_de' => 'Ersatz', 'value_es' => 'Suplente'],
            ['translation_key' => 'schedule.location_notes', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Location Notes', 'value_fr' => 'Notes sur le lieu', 'value_de' => 'Standorthinweise', 'value_es' => 'Notas de ubicación'],
            ['translation_key' => 'schedule.meeting_instructions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Meeting Instructions', 'value_fr' => 'Instructions de réunion', 'value_de' => 'Treffpunktanweisungen', 'value_es' => 'Instrucciones de reunión'],
            ['translation_key' => 'schedule.access_notes', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Access Notes', 'value_fr' => 'Notes d\'accès', 'value_de' => 'Zugangshinweise', 'value_es' => 'Notas de acceso'],
            ['translation_key' => 'schedule.internal_notes', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Internal Notes', 'value_fr' => 'Notes internes', 'value_de' => 'Interne Notizen', 'value_es' => 'Notas internas'],
            ['translation_key' => 'schedule.recurring_sessions', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Recurring Sessions', 'value_fr' => 'Sessions récurrentes', 'value_de' => 'Wiederkehrende Sitzungen', 'value_es' => 'Sesiones recurrentes'],
            ['translation_key' => 'schedule.booking_stats', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Booking Stats', 'value_fr' => 'Statistiques de réservation', 'value_de' => 'Buchungsstatistiken', 'value_es' => 'Estadísticas de reservas'],
            ['translation_key' => 'schedule.booked', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Booked', 'value_fr' => 'Réservé', 'value_de' => 'Gebucht', 'value_es' => 'Reservado'],
            ['translation_key' => 'schedule.intake_done', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Intake Done', 'value_fr' => 'Admission terminée', 'value_de' => 'Aufnahme erledigt', 'value_es' => 'Admisión completada'],
            ['translation_key' => 'schedule.intake_pending', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Intake Pending', 'value_fr' => 'Admission en attente', 'value_de' => 'Aufnahme ausstehend', 'value_es' => 'Admisión pendiente'],
            ['translation_key' => 'schedule.no_bookings_yet', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No Bookings Yet', 'value_fr' => 'Pas encore de réservations', 'value_de' => 'Noch keine Buchungen', 'value_es' => 'Sin reservas aún'],
            ['translation_key' => 'schedule.no_one_booked', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No one has booked this class session yet.', 'value_fr' => 'Personne n\'a encore réservé cette session.', 'value_de' => 'Niemand hat diese Sitzung noch gebucht.', 'value_es' => 'Nadie ha reservado esta sesión todavía.'],
            ['translation_key' => 'schedule.check_in', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Check In', 'value_fr' => 'Enregistrement', 'value_de' => 'Einchecken', 'value_es' => 'Registrar'],
            ['translation_key' => 'schedule.already_checked_in', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Already Checked In', 'value_fr' => 'Déjà enregistré', 'value_de' => 'Bereits eingecheckt', 'value_es' => 'Ya registrado'],
            ['translation_key' => 'schedule.view_booking', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'View Booking', 'value_fr' => 'Voir la réservation', 'value_de' => 'Buchung anzeigen', 'value_es' => 'Ver reserva'],
            ['translation_key' => 'schedule.view_intake_form', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'View Intake Form', 'value_fr' => 'Voir le formulaire d\'admission', 'value_de' => 'Aufnahmeformular anzeigen', 'value_es' => 'Ver formulario de admisión'],
            ['translation_key' => 'schedule.intake_form_responses', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Intake Form Responses', 'value_fr' => 'Réponses au formulaire d\'admission', 'value_de' => 'Aufnahmeformular-Antworten', 'value_es' => 'Respuestas del formulario de admisión'],
            ['translation_key' => 'schedule.no_answer_provided', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'No answer provided', 'value_fr' => 'Aucune réponse fournie', 'value_de' => 'Keine Antwort gegeben', 'value_es' => 'Sin respuesta'],
            ['translation_key' => 'schedule.confirm_cancel_class', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Are you sure you want to cancel this class session?', 'value_fr' => 'Êtes-vous sûr de vouloir annuler cette session ?', 'value_de' => 'Sind Sie sicher, dass Sie diese Sitzung stornieren möchten?', 'value_es' => '¿Estás seguro de que quieres cancelar esta sesión?'],
            ['translation_key' => 'schedule.reason_optional', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Reason (optional)', 'value_fr' => 'Raison (facultatif)', 'value_de' => 'Grund (optional)', 'value_es' => 'Razón (opcional)'],
            ['translation_key' => 'schedule.enter_reason', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Enter a reason for cancellation...', 'value_fr' => 'Entrez une raison d\'annulation...', 'value_de' => 'Geben Sie einen Stornierungsgrund ein...', 'value_es' => 'Ingresa una razón de cancelación...'],
            ['translation_key' => 'schedule.keep_session', 'category' => 'general_content', 'page_context' => 'schedule',
             'value_en' => 'Keep Session', 'value_fr' => 'Garder la session', 'value_de' => 'Sitzung behalten', 'value_es' => 'Mantener sesión'],
        ];
    }

    /**
     * Instructor page translations.
     */
    protected function getInstructorTranslations(): array
    {
        return [
            ['translation_key' => 'instructors.title', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Instructors', 'value_fr' => 'Instructeurs', 'value_de' => 'Trainer', 'value_es' => 'Instructores'],
            ['translation_key' => 'instructors.add_instructor', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Add Instructor', 'value_fr' => 'Ajouter un instructeur', 'value_de' => 'Trainer hinzufügen', 'value_es' => 'Añadir instructor'],
            ['translation_key' => 'instructors.edit_instructor', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Edit Instructor', 'value_fr' => 'Modifier l\'instructeur', 'value_de' => 'Trainer bearbeiten', 'value_es' => 'Editar instructor'],
            ['translation_key' => 'instructors.instructor_details', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Instructor Details', 'value_fr' => 'Détails de l\'instructeur', 'value_de' => 'Trainerdetails', 'value_es' => 'Detalles del instructor'],
            ['translation_key' => 'instructors.bio', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Biography', 'value_fr' => 'Biographie', 'value_de' => 'Biografie', 'value_es' => 'Biografía'],
            ['translation_key' => 'instructors.specialties', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Specialties', 'value_fr' => 'Spécialités', 'value_de' => 'Spezialisierungen', 'value_es' => 'Especialidades'],
            ['translation_key' => 'instructors.certifications', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Certifications', 'value_fr' => 'Certifications', 'value_de' => 'Zertifizierungen', 'value_es' => 'Certificaciones'],
            ['translation_key' => 'instructors.availability', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Availability', 'value_fr' => 'Disponibilité', 'value_de' => 'Verfügbarkeit', 'value_es' => 'Disponibilidad'],
            ['translation_key' => 'instructors.assigned_classes', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Assigned Classes', 'value_fr' => 'Cours assignés', 'value_de' => 'Zugewiesene Kurse', 'value_es' => 'Clases asignadas'],
            ['translation_key' => 'instructors.payouts', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Payouts', 'value_fr' => 'Paiements', 'value_de' => 'Auszahlungen', 'value_es' => 'Pagos'],
            ['translation_key' => 'instructors.hourly_rate', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Hourly Rate', 'value_fr' => 'Tarif horaire', 'value_de' => 'Stundensatz', 'value_es' => 'Tarifa por hora'],
            ['translation_key' => 'instructors.classes_taught', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Classes Taught', 'value_fr' => 'Cours dispensés', 'value_de' => 'Unterrichtete Kurse', 'value_es' => 'Clases impartidas'],
            ['translation_key' => 'instructors.rating', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'Rating', 'value_fr' => 'Évaluation', 'value_de' => 'Bewertung', 'value_es' => 'Calificación'],
            ['translation_key' => 'instructors.no_instructors', 'category' => 'general_content', 'page_context' => 'instructors',
             'value_en' => 'No instructors found', 'value_fr' => 'Aucun instructeur trouvé', 'value_de' => 'Keine Trainer gefunden', 'value_es' => 'No se encontraron instructores'],
        ];
    }

    /**
     * Payment page translations.
     */
    protected function getPaymentTranslations(): array
    {
        return [
            ['translation_key' => 'payments.title', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Payments', 'value_fr' => 'Paiements', 'value_de' => 'Zahlungen', 'value_es' => 'Pagos'],
            ['translation_key' => 'payments.transactions', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Transactions', 'value_fr' => 'Transactions', 'value_de' => 'Transaktionen', 'value_es' => 'Transacciones'],
            ['translation_key' => 'payments.all_transactions', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'All Transactions', 'value_fr' => 'Toutes les transactions', 'value_de' => 'Alle Transaktionen', 'value_es' => 'Todas las transacciones'],
            ['translation_key' => 'payments.amount', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Amount', 'value_fr' => 'Montant', 'value_de' => 'Betrag', 'value_es' => 'Monto'],
            ['translation_key' => 'payments.status', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Status', 'value_fr' => 'Statut', 'value_de' => 'Status', 'value_es' => 'Estado'],
            ['translation_key' => 'payments.paid', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Paid', 'value_fr' => 'Payé', 'value_de' => 'Bezahlt', 'value_es' => 'Pagado'],
            ['translation_key' => 'payments.pending', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Pending', 'value_fr' => 'En attente', 'value_de' => 'Ausstehend', 'value_es' => 'Pendiente'],
            ['translation_key' => 'payments.failed', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Failed', 'value_fr' => 'Échoué', 'value_de' => 'Fehlgeschlagen', 'value_es' => 'Fallido'],
            ['translation_key' => 'payments.refunded', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Refunded', 'value_fr' => 'Remboursé', 'value_de' => 'Erstattet', 'value_es' => 'Reembolsado'],
            ['translation_key' => 'payments.payment_method', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Payment Method', 'value_fr' => 'Mode de paiement', 'value_de' => 'Zahlungsmethode', 'value_es' => 'Método de pago'],
            ['translation_key' => 'payments.card', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Card', 'value_fr' => 'Carte', 'value_de' => 'Karte', 'value_es' => 'Tarjeta'],
            ['translation_key' => 'payments.cash', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Cash', 'value_fr' => 'Espèces', 'value_de' => 'Bargeld', 'value_es' => 'Efectivo'],
            ['translation_key' => 'payments.invoice', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Invoice', 'value_fr' => 'Facture', 'value_de' => 'Rechnung', 'value_es' => 'Factura'],
            ['translation_key' => 'payments.view_invoice', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'View Invoice', 'value_fr' => 'Voir la facture', 'value_de' => 'Rechnung anzeigen', 'value_es' => 'Ver factura'],
            ['translation_key' => 'payments.download_invoice', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Download Invoice', 'value_fr' => 'Télécharger la facture', 'value_de' => 'Rechnung herunterladen', 'value_es' => 'Descargar factura'],
            ['translation_key' => 'payments.issue_refund', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Issue Refund', 'value_fr' => 'Émettre un remboursement', 'value_de' => 'Rückerstattung ausstellen', 'value_es' => 'Emitir reembolso'],
            ['translation_key' => 'payments.subscriptions', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'Subscriptions', 'value_fr' => 'Abonnements', 'value_de' => 'Abonnements', 'value_es' => 'Suscripciones'],
            ['translation_key' => 'payments.no_transactions', 'category' => 'general_content', 'page_context' => 'payments',
             'value_en' => 'No transactions found', 'value_fr' => 'Aucune transaction trouvée', 'value_de' => 'Keine Transaktionen gefunden', 'value_es' => 'No se encontraron transacciones'],

            // Bookings page
            ['translation_key' => 'bookings.booking', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Booking', 'value_fr' => 'Réservation', 'value_de' => 'Buchung', 'value_es' => 'Reserva'],
            ['translation_key' => 'bookings.all_bookings', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'All Bookings', 'value_fr' => 'Toutes les réservations', 'value_de' => 'Alle Buchungen', 'value_es' => 'Todas las reservas'],
            ['translation_key' => 'bookings.upcoming', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Upcoming', 'value_fr' => 'À venir', 'value_de' => 'Bevorstehend', 'value_es' => 'Próximas'],
            ['translation_key' => 'bookings.cancellations', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Cancellations', 'value_fr' => 'Annulations', 'value_de' => 'Stornierungen', 'value_es' => 'Cancelaciones'],
            ['translation_key' => 'bookings.no_shows', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'No-Shows', 'value_fr' => 'Absences', 'value_de' => 'Nichterscheinen', 'value_es' => 'Ausencias'],
            ['translation_key' => 'bookings.search_placeholder', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Search by client name or email...', 'value_fr' => 'Rechercher par nom ou email...', 'value_de' => 'Nach Name oder E-Mail suchen...', 'value_es' => 'Buscar por nombre o correo...'],
            ['translation_key' => 'bookings.source', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Source', 'value_fr' => 'Source', 'value_de' => 'Quelle', 'value_es' => 'Origen'],
            ['translation_key' => 'bookings.payment', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Payment', 'value_fr' => 'Paiement', 'value_de' => 'Zahlung', 'value_es' => 'Pago'],
            ['translation_key' => 'bookings.all_sources', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'All Sources', 'value_fr' => 'Toutes les sources', 'value_de' => 'Alle Quellen', 'value_es' => 'Todos los orígenes'],
            ['translation_key' => 'bookings.all_methods', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'All Methods', 'value_fr' => 'Toutes les méthodes', 'value_de' => 'Alle Methoden', 'value_es' => 'Todos los métodos'],
            ['translation_key' => 'bookings.no_bookings_found', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'No Bookings Found', 'value_fr' => 'Aucune réservation trouvée', 'value_de' => 'Keine Buchungen gefunden', 'value_es' => 'No se encontraron reservas'],
            ['translation_key' => 'bookings.no_upcoming', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'There are no upcoming bookings at the moment.', 'value_fr' => 'Il n\'y a pas de réservations à venir pour le moment.', 'value_de' => 'Es gibt derzeit keine bevorstehenden Buchungen.', 'value_es' => 'No hay reservas próximas en este momento.'],
            ['translation_key' => 'bookings.no_cancelled', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'No bookings have been cancelled.', 'value_fr' => 'Aucune réservation n\'a été annulée.', 'value_de' => 'Keine Buchungen wurden storniert.', 'value_es' => 'No se han cancelado reservas.'],
            ['translation_key' => 'bookings.no_no_shows', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'No clients have been marked as no-shows.', 'value_fr' => 'Aucun client n\'a été marqué absent.', 'value_de' => 'Keine Kunden wurden als nicht erschienen markiert.', 'value_es' => 'Ningún cliente ha sido marcado como ausente.'],
            ['translation_key' => 'bookings.no_match_search', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'No bookings match your search criteria.', 'value_fr' => 'Aucune réservation ne correspond à vos critères.', 'value_de' => 'Keine Buchungen entsprechen Ihren Suchkriterien.', 'value_es' => 'Ninguna reserva coincide con tu búsqueda.'],
            ['translation_key' => 'bookings.date_time', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Date/Time', 'value_fr' => 'Date/Heure', 'value_de' => 'Datum/Zeit', 'value_es' => 'Fecha/Hora'],
            ['translation_key' => 'bookings.class_service', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Class/Service', 'value_fr' => 'Cours/Service', 'value_de' => 'Kurs/Dienstleistung', 'value_es' => 'Clase/Servicio'],
            ['translation_key' => 'bookings.unknown_client', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Unknown Client', 'value_fr' => 'Client inconnu', 'value_de' => 'Unbekannter Kunde', 'value_es' => 'Cliente desconocido'],
            ['translation_key' => 'bookings.checked_in', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'Checked in', 'value_fr' => 'Enregistré', 'value_de' => 'Eingecheckt', 'value_es' => 'Registrado'],
            ['translation_key' => 'bookings.view_details', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'View Details', 'value_fr' => 'Voir les détails', 'value_de' => 'Details anzeigen', 'value_es' => 'Ver detalles'],
            ['translation_key' => 'bookings.view_client', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'View Client', 'value_fr' => 'Voir le client', 'value_de' => 'Kunde anzeigen', 'value_es' => 'Ver cliente'],
            ['translation_key' => 'bookings.view_session', 'category' => 'general_content', 'page_context' => 'bookings',
             'value_en' => 'View Session', 'value_fr' => 'Voir la session', 'value_de' => 'Sitzung anzeigen', 'value_es' => 'Ver sesión'],
        ];
    }

    /**
     * Settings page translations.
     */
    protected function getSettingsTranslations(): array
    {
        return [
            // General settings
            ['translation_key' => 'settings.title', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Settings', 'value_fr' => 'Paramètres', 'value_de' => 'Einstellungen', 'value_es' => 'Configuración'],
            ['translation_key' => 'settings.studio_profile', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Studio Profile', 'value_fr' => 'Profil du studio', 'value_de' => 'Studio-Profil', 'value_es' => 'Perfil del estudio'],
            ['translation_key' => 'settings.business_info', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Business Information', 'value_fr' => 'Informations commerciales', 'value_de' => 'Geschäftsinformationen', 'value_es' => 'Información comercial'],
            ['translation_key' => 'settings.studio_name', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Studio Name', 'value_fr' => 'Nom du studio', 'value_de' => 'Studioname', 'value_es' => 'Nombre del estudio'],
            ['translation_key' => 'settings.logo', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Logo', 'value_fr' => 'Logo', 'value_de' => 'Logo', 'value_es' => 'Logotipo'],
            ['translation_key' => 'settings.upload_logo', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Upload Logo', 'value_fr' => 'Télécharger le logo', 'value_de' => 'Logo hochladen', 'value_es' => 'Subir logotipo'],
            ['translation_key' => 'settings.timezone', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Timezone', 'value_fr' => 'Fuseau horaire', 'value_de' => 'Zeitzone', 'value_es' => 'Zona horaria'],
            ['translation_key' => 'settings.currency', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Currency', 'value_fr' => 'Devise', 'value_de' => 'Währung', 'value_es' => 'Moneda'],
            ['translation_key' => 'settings.language', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Language', 'value_fr' => 'Langue', 'value_de' => 'Sprache', 'value_es' => 'Idioma'],

            // Locations
            ['translation_key' => 'settings.locations', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Locations', 'value_fr' => 'Emplacements', 'value_de' => 'Standorte', 'value_es' => 'Ubicaciones'],
            ['translation_key' => 'settings.add_location', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Add Location', 'value_fr' => 'Ajouter un emplacement', 'value_de' => 'Standort hinzufügen', 'value_es' => 'Añadir ubicación'],
            ['translation_key' => 'settings.rooms', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Rooms', 'value_fr' => 'Salles', 'value_de' => 'Räume', 'value_es' => 'Salas'],
            ['translation_key' => 'settings.add_room', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Add Room', 'value_fr' => 'Ajouter une salle', 'value_de' => 'Raum hinzufügen', 'value_es' => 'Añadir sala'],

            // Team
            ['translation_key' => 'settings.team', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Team', 'value_fr' => 'Équipe', 'value_de' => 'Team', 'value_es' => 'Equipo'],
            ['translation_key' => 'settings.team_members', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Team Members', 'value_fr' => 'Membres de l\'équipe', 'value_de' => 'Teammitglieder', 'value_es' => 'Miembros del equipo'],
            ['translation_key' => 'settings.invite_member', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Invite Member', 'value_fr' => 'Inviter un membre', 'value_de' => 'Mitglied einladen', 'value_es' => 'Invitar miembro'],
            ['translation_key' => 'settings.roles', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Roles', 'value_fr' => 'Rôles', 'value_de' => 'Rollen', 'value_es' => 'Roles'],
            ['translation_key' => 'settings.permissions', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Permissions', 'value_fr' => 'Autorisations', 'value_de' => 'Berechtigungen', 'value_es' => 'Permisos'],

            // Integrations
            ['translation_key' => 'settings.integrations', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Integrations', 'value_fr' => 'Intégrations', 'value_de' => 'Integrationen', 'value_es' => 'Integraciones'],
            ['translation_key' => 'settings.connected', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Connected', 'value_fr' => 'Connecté', 'value_de' => 'Verbunden', 'value_es' => 'Conectado'],
            ['translation_key' => 'settings.not_connected', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Not Connected', 'value_fr' => 'Non connecté', 'value_de' => 'Nicht verbunden', 'value_es' => 'No conectado'],
            ['translation_key' => 'settings.connect', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Connect', 'value_fr' => 'Connecter', 'value_de' => 'Verbinden', 'value_es' => 'Conectar'],
            ['translation_key' => 'settings.disconnect', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Disconnect', 'value_fr' => 'Déconnecter', 'value_de' => 'Trennen', 'value_es' => 'Desconectar'],

            // Notifications
            ['translation_key' => 'settings.notifications', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Notifications', 'value_fr' => 'Notifications', 'value_de' => 'Benachrichtigungen', 'value_es' => 'Notificaciones'],
            ['translation_key' => 'settings.email_notifications', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Email Notifications', 'value_fr' => 'Notifications par e-mail', 'value_de' => 'E-Mail-Benachrichtigungen', 'value_es' => 'Notificaciones por correo'],
            ['translation_key' => 'settings.sms_notifications', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'SMS Notifications', 'value_fr' => 'Notifications SMS', 'value_de' => 'SMS-Benachrichtigungen', 'value_es' => 'Notificaciones SMS'],

            // Billing
            ['translation_key' => 'settings.billing', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Billing', 'value_fr' => 'Facturation', 'value_de' => 'Abrechnung', 'value_es' => 'Facturación'],
            ['translation_key' => 'settings.subscription_plan', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Subscription Plan', 'value_fr' => 'Plan d\'abonnement', 'value_de' => 'Abonnementplan', 'value_es' => 'Plan de suscripción'],
            ['translation_key' => 'settings.payment_method', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Payment Method', 'value_fr' => 'Mode de paiement', 'value_de' => 'Zahlungsmethode', 'value_es' => 'Método de pago'],
            ['translation_key' => 'settings.billing_history', 'category' => 'general_content', 'page_context' => 'settings',
             'value_en' => 'Billing History', 'value_fr' => 'Historique de facturation', 'value_de' => 'Abrechnungsverlauf', 'value_es' => 'Historial de facturación'],
        ];
    }

    /**
     * Subdomain public pages translations.
     */
    protected function getSubdomainTranslations(): array
    {
        return [
            // Home page
            ['translation_key' => 'subdomain.home.welcome', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Welcome to', 'value_fr' => 'Bienvenue chez', 'value_de' => 'Willkommen bei', 'value_es' => 'Bienvenido a'],
            ['translation_key' => 'subdomain.home.book_class', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Book a Class', 'value_fr' => 'Réserver un cours', 'value_de' => 'Kurs buchen', 'value_es' => 'Reservar una clase'],
            ['translation_key' => 'subdomain.home.view_schedule', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'View Schedule', 'value_fr' => 'Voir le programme', 'value_de' => 'Zeitplan anzeigen', 'value_es' => 'Ver horario'],
            ['translation_key' => 'subdomain.home.our_classes', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Our Classes', 'value_fr' => 'Nos cours', 'value_de' => 'Unsere Kurse', 'value_es' => 'Nuestras clases'],
            ['translation_key' => 'subdomain.home.our_services', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Our Services', 'value_fr' => 'Nos services', 'value_de' => 'Unsere Dienstleistungen', 'value_es' => 'Nuestros servicios'],
            ['translation_key' => 'subdomain.home.our_instructors', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Our Instructors', 'value_fr' => 'Nos instructeurs', 'value_de' => 'Unsere Trainer', 'value_es' => 'Nuestros instructores'],
            ['translation_key' => 'subdomain.home.memberships', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Memberships', 'value_fr' => 'Abonnements', 'value_de' => 'Mitgliedschaften', 'value_es' => 'Membresías'],
            ['translation_key' => 'subdomain.home.contact_us', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Contact Us', 'value_fr' => 'Contactez-nous', 'value_de' => 'Kontaktieren Sie uns', 'value_es' => 'Contáctenos'],
            ['translation_key' => 'subdomain.home.location', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Location', 'value_fr' => 'Emplacement', 'value_de' => 'Standort', 'value_es' => 'Ubicación'],
            ['translation_key' => 'subdomain.home.hours', 'category' => 'general_content', 'page_context' => 'subdomain_home',
             'value_en' => 'Hours', 'value_fr' => 'Horaires', 'value_de' => 'Öffnungszeiten', 'value_es' => 'Horarios'],

            // Schedule page
            ['translation_key' => 'subdomain.schedule.title', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => 'Class Schedule', 'value_fr' => 'Programme des cours', 'value_de' => 'Kursplan', 'value_es' => 'Horario de clases'],
            ['translation_key' => 'subdomain.schedule.filter_by_class', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => 'Filter by Class', 'value_fr' => 'Filtrer par cours', 'value_de' => 'Nach Kurs filtern', 'value_es' => 'Filtrar por clase'],
            ['translation_key' => 'subdomain.schedule.filter_by_instructor', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => 'Filter by Instructor', 'value_fr' => 'Filtrer par instructeur', 'value_de' => 'Nach Trainer filtern', 'value_es' => 'Filtrar por instructor'],
            ['translation_key' => 'subdomain.schedule.all_classes', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => 'All Classes', 'value_fr' => 'Tous les cours', 'value_de' => 'Alle Kurse', 'value_es' => 'Todas las clases'],
            ['translation_key' => 'subdomain.schedule.all_instructors', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => 'All Instructors', 'value_fr' => 'Tous les instructeurs', 'value_de' => 'Alle Trainer', 'value_es' => 'Todos los instructores'],
            ['translation_key' => 'subdomain.schedule.spots_left', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => ':count spots left', 'value_fr' => ':count places restantes', 'value_de' => ':count Plätze frei', 'value_es' => ':count plazas disponibles'],
            ['translation_key' => 'subdomain.schedule.full', 'category' => 'general_content', 'page_context' => 'subdomain_schedule',
             'value_en' => 'Full', 'value_fr' => 'Complet', 'value_de' => 'Voll', 'value_es' => 'Lleno'],

            // Class details
            ['translation_key' => 'subdomain.class.details', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Class Details', 'value_fr' => 'Détails du cours', 'value_de' => 'Kursdetails', 'value_es' => 'Detalles de la clase'],
            ['translation_key' => 'subdomain.class.description', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Description', 'value_fr' => 'Description', 'value_de' => 'Beschreibung', 'value_es' => 'Descripción'],
            ['translation_key' => 'subdomain.class.instructor', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Instructor', 'value_fr' => 'Instructeur', 'value_de' => 'Trainer', 'value_es' => 'Instructor'],
            ['translation_key' => 'subdomain.class.duration', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Duration', 'value_fr' => 'Durée', 'value_de' => 'Dauer', 'value_es' => 'Duración'],
            ['translation_key' => 'subdomain.class.difficulty', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Difficulty', 'value_fr' => 'Difficulté', 'value_de' => 'Schwierigkeit', 'value_es' => 'Dificultad'],
            ['translation_key' => 'subdomain.class.beginner', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Beginner', 'value_fr' => 'Débutant', 'value_de' => 'Anfänger', 'value_es' => 'Principiante'],
            ['translation_key' => 'subdomain.class.intermediate', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Intermediate', 'value_fr' => 'Intermédiaire', 'value_de' => 'Fortgeschritten', 'value_es' => 'Intermedio'],
            ['translation_key' => 'subdomain.class.advanced', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'Advanced', 'value_fr' => 'Avancé', 'value_de' => 'Experte', 'value_es' => 'Avanzado'],
            ['translation_key' => 'subdomain.class.all_levels', 'category' => 'general_content', 'page_context' => 'subdomain_class',
             'value_en' => 'All Levels', 'value_fr' => 'Tous niveaux', 'value_de' => 'Alle Levels', 'value_es' => 'Todos los niveles'],

            // Service request
            ['translation_key' => 'subdomain.service_request.title', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Request a Service', 'value_fr' => 'Demander un service', 'value_de' => 'Service anfragen', 'value_es' => 'Solicitar un servicio'],
            ['translation_key' => 'subdomain.service_request.select_service', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Select a Service', 'value_fr' => 'Sélectionner un service', 'value_de' => 'Service auswählen', 'value_es' => 'Seleccionar un servicio'],
            ['translation_key' => 'subdomain.service_request.preferred_date', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Preferred Date', 'value_fr' => 'Date préférée', 'value_de' => 'Bevorzugtes Datum', 'value_es' => 'Fecha preferida'],
            ['translation_key' => 'subdomain.service_request.preferred_time', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Preferred Time', 'value_fr' => 'Heure préférée', 'value_de' => 'Bevorzugte Zeit', 'value_es' => 'Hora preferida'],
            ['translation_key' => 'subdomain.service_request.additional_notes', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Additional Notes', 'value_fr' => 'Notes supplémentaires', 'value_de' => 'Zusätzliche Anmerkungen', 'value_es' => 'Notas adicionales'],
            ['translation_key' => 'subdomain.service_request.submit', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Submit Request', 'value_fr' => 'Envoyer la demande', 'value_de' => 'Anfrage senden', 'value_es' => 'Enviar solicitud'],
            ['translation_key' => 'subdomain.service_request.success', 'category' => 'general_content', 'page_context' => 'subdomain_service',
             'value_en' => 'Your request has been submitted successfully!', 'value_fr' => 'Votre demande a été soumise avec succès !', 'value_de' => 'Ihre Anfrage wurde erfolgreich eingereicht!', 'value_es' => '¡Su solicitud ha sido enviada con éxito!'],

            // Instructors page
            ['translation_key' => 'subdomain.instructors.title', 'category' => 'general_content', 'page_context' => 'subdomain_instructors',
             'value_en' => 'Our Instructors', 'value_fr' => 'Nos instructeurs', 'value_de' => 'Unsere Trainer', 'value_es' => 'Nuestros instructores'],
            ['translation_key' => 'subdomain.instructors.meet_team', 'category' => 'general_content', 'page_context' => 'subdomain_instructors',
             'value_en' => 'Meet Our Team', 'value_fr' => 'Rencontrez notre équipe', 'value_de' => 'Lernen Sie unser Team kennen', 'value_es' => 'Conozca a nuestro equipo'],
            ['translation_key' => 'subdomain.instructors.view_profile', 'category' => 'general_content', 'page_context' => 'subdomain_instructors',
             'value_en' => 'View Profile', 'value_fr' => 'Voir le profil', 'value_de' => 'Profil anzeigen', 'value_es' => 'Ver perfil'],
            ['translation_key' => 'subdomain.instructors.book_with', 'category' => 'general_content', 'page_context' => 'subdomain_instructors',
             'value_en' => 'Book with :name', 'value_fr' => 'Réserver avec :name', 'value_de' => 'Buchen mit :name', 'value_es' => 'Reservar con :name'],

            // Login/Signup
            ['translation_key' => 'subdomain.auth.login', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Login', 'value_fr' => 'Connexion', 'value_de' => 'Anmelden', 'value_es' => 'Iniciar sesión'],
            ['translation_key' => 'subdomain.auth.signup', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Sign Up', 'value_fr' => 'S\'inscrire', 'value_de' => 'Registrieren', 'value_es' => 'Registrarse'],
            ['translation_key' => 'subdomain.auth.email', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Email', 'value_fr' => 'E-mail', 'value_de' => 'E-Mail', 'value_es' => 'Correo electrónico'],
            ['translation_key' => 'subdomain.auth.password', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Password', 'value_fr' => 'Mot de passe', 'value_de' => 'Passwort', 'value_es' => 'Contraseña'],
            ['translation_key' => 'subdomain.auth.confirm_password', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Confirm Password', 'value_fr' => 'Confirmer le mot de passe', 'value_de' => 'Passwort bestätigen', 'value_es' => 'Confirmar contraseña'],
            ['translation_key' => 'subdomain.auth.forgot_password', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Forgot Password?', 'value_fr' => 'Mot de passe oublié ?', 'value_de' => 'Passwort vergessen?', 'value_es' => '¿Olvidó su contraseña?'],
            ['translation_key' => 'subdomain.auth.remember_me', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Remember me', 'value_fr' => 'Se souvenir de moi', 'value_de' => 'Angemeldet bleiben', 'value_es' => 'Recordarme'],
            ['translation_key' => 'subdomain.auth.no_account', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Don\'t have an account?', 'value_fr' => 'Vous n\'avez pas de compte ?', 'value_de' => 'Noch kein Konto?', 'value_es' => '¿No tiene una cuenta?'],
            ['translation_key' => 'subdomain.auth.have_account', 'category' => 'general_content', 'page_context' => 'subdomain_auth',
             'value_en' => 'Already have an account?', 'value_fr' => 'Vous avez déjà un compte ?', 'value_de' => 'Bereits ein Konto?', 'value_es' => '¿Ya tiene una cuenta?'],
        ];
    }

    /**
     * Member portal translations.
     */
    protected function getMemberPortalTranslations(): array
    {
        return [
            // Portal navigation
            ['translation_key' => 'member.portal.title', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Member Portal', 'value_fr' => 'Portail membre', 'value_de' => 'Mitgliederportal', 'value_es' => 'Portal de miembros'],
            ['translation_key' => 'member.portal.dashboard', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Dashboard', 'value_fr' => 'Tableau de bord', 'value_de' => 'Dashboard', 'value_es' => 'Panel'],
            ['translation_key' => 'member.portal.my_bookings', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Bookings', 'value_fr' => 'Mes réservations', 'value_de' => 'Meine Buchungen', 'value_es' => 'Mis reservas'],
            ['translation_key' => 'member.portal.my_memberships', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Memberships', 'value_fr' => 'Mes abonnements', 'value_de' => 'Meine Mitgliedschaften', 'value_es' => 'Mis membresías'],
            ['translation_key' => 'member.portal.my_payments', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Payments', 'value_fr' => 'Mes paiements', 'value_de' => 'Meine Zahlungen', 'value_es' => 'Mis pagos'],
            ['translation_key' => 'member.portal.my_profile', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Profile', 'value_fr' => 'Mon profil', 'value_de' => 'Mein Profil', 'value_es' => 'Mi perfil'],
            ['translation_key' => 'member.portal.book_class', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Book a Class', 'value_fr' => 'Réserver un cours', 'value_de' => 'Kurs buchen', 'value_es' => 'Reservar una clase'],
            ['translation_key' => 'member.portal.logout', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Logout', 'value_fr' => 'Déconnexion', 'value_de' => 'Abmelden', 'value_es' => 'Cerrar sesión'],

            // Dashboard
            ['translation_key' => 'member.dashboard.welcome', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Welcome back', 'value_fr' => 'Bon retour', 'value_de' => 'Willkommen zurück', 'value_es' => 'Bienvenido de nuevo'],
            ['translation_key' => 'member.dashboard.upcoming_bookings', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Upcoming Bookings', 'value_fr' => 'Réservations à venir', 'value_de' => 'Kommende Buchungen', 'value_es' => 'Próximas reservas'],
            ['translation_key' => 'member.dashboard.no_upcoming', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'No upcoming bookings', 'value_fr' => 'Aucune réservation à venir', 'value_de' => 'Keine kommenden Buchungen', 'value_es' => 'No hay reservas próximas'],
            ['translation_key' => 'member.dashboard.membership_status', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Membership Status', 'value_fr' => 'Statut d\'adhésion', 'value_de' => 'Mitgliedschaftsstatus', 'value_es' => 'Estado de membresía'],
            ['translation_key' => 'member.dashboard.classes_remaining', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Classes Remaining', 'value_fr' => 'Cours restants', 'value_de' => 'Verbleibende Kurse', 'value_es' => 'Clases restantes'],
            ['translation_key' => 'member.dashboard.valid_until', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Valid Until', 'value_fr' => 'Valable jusqu\'au', 'value_de' => 'Gültig bis', 'value_es' => 'Válido hasta'],

            // Bookings
            ['translation_key' => 'member.bookings.title', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Bookings', 'value_fr' => 'Mes réservations', 'value_de' => 'Meine Buchungen', 'value_es' => 'Mis reservas'],
            ['translation_key' => 'member.bookings.upcoming', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Upcoming', 'value_fr' => 'À venir', 'value_de' => 'Bevorstehend', 'value_es' => 'Próximas'],
            ['translation_key' => 'member.bookings.past', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Past', 'value_fr' => 'Passées', 'value_de' => 'Vergangen', 'value_es' => 'Pasadas'],
            ['translation_key' => 'member.bookings.cancelled', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Cancelled', 'value_fr' => 'Annulées', 'value_de' => 'Storniert', 'value_es' => 'Canceladas'],
            ['translation_key' => 'member.bookings.no_bookings', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'No bookings found', 'value_fr' => 'Aucune réservation trouvée', 'value_de' => 'Keine Buchungen gefunden', 'value_es' => 'No se encontraron reservas'],
            ['translation_key' => 'member.bookings.cancel', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Cancel Booking', 'value_fr' => 'Annuler la réservation', 'value_de' => 'Buchung stornieren', 'value_es' => 'Cancelar reserva'],
            ['translation_key' => 'member.bookings.confirmed', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Confirmed', 'value_fr' => 'Confirmée', 'value_de' => 'Bestätigt', 'value_es' => 'Confirmada'],
            ['translation_key' => 'member.bookings.attended', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Attended', 'value_fr' => 'Présent', 'value_de' => 'Teilgenommen', 'value_es' => 'Asistió'],
            ['translation_key' => 'member.bookings.no_show', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'No Show', 'value_fr' => 'Absent', 'value_de' => 'Nicht erschienen', 'value_es' => 'No asistió'],

            // Memberships
            ['translation_key' => 'member.memberships.title', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Memberships', 'value_fr' => 'Mes abonnements', 'value_de' => 'Meine Mitgliedschaften', 'value_es' => 'Mis membresías'],
            ['translation_key' => 'member.memberships.active', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Active', 'value_fr' => 'Actif', 'value_de' => 'Aktiv', 'value_es' => 'Activa'],
            ['translation_key' => 'member.memberships.expired', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Expired', 'value_fr' => 'Expiré', 'value_de' => 'Abgelaufen', 'value_es' => 'Expirada'],
            ['translation_key' => 'member.memberships.renew', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Renew', 'value_fr' => 'Renouveler', 'value_de' => 'Erneuern', 'value_es' => 'Renovar'],
            ['translation_key' => 'member.memberships.buy_membership', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Buy Membership', 'value_fr' => 'Acheter un abonnement', 'value_de' => 'Mitgliedschaft kaufen', 'value_es' => 'Comprar membresía'],
            ['translation_key' => 'member.memberships.no_memberships', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'No active memberships', 'value_fr' => 'Aucun abonnement actif', 'value_de' => 'Keine aktiven Mitgliedschaften', 'value_es' => 'No hay membresías activas'],

            // Profile
            ['translation_key' => 'member.profile.title', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'My Profile', 'value_fr' => 'Mon profil', 'value_de' => 'Mein Profil', 'value_es' => 'Mi perfil'],
            ['translation_key' => 'member.profile.personal_info', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Personal Information', 'value_fr' => 'Informations personnelles', 'value_de' => 'Persönliche Informationen', 'value_es' => 'Información personal'],
            ['translation_key' => 'member.profile.change_password', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Change Password', 'value_fr' => 'Changer le mot de passe', 'value_de' => 'Passwort ändern', 'value_es' => 'Cambiar contraseña'],
            ['translation_key' => 'member.profile.current_password', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Current Password', 'value_fr' => 'Mot de passe actuel', 'value_de' => 'Aktuelles Passwort', 'value_es' => 'Contraseña actual'],
            ['translation_key' => 'member.profile.new_password', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'New Password', 'value_fr' => 'Nouveau mot de passe', 'value_de' => 'Neues Passwort', 'value_es' => 'Nueva contraseña'],
            ['translation_key' => 'member.profile.update_profile', 'category' => 'general_content', 'page_context' => 'member_portal',
             'value_en' => 'Update Profile', 'value_fr' => 'Mettre à jour le profil', 'value_de' => 'Profil aktualisieren', 'value_es' => 'Actualizar perfil'],
        ];
    }

    /**
     * Common/shared translations.
     */
    protected function getCommonTranslations(): array
    {
        return [
            // Time-related
            ['translation_key' => 'common.minutes', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'minutes', 'value_fr' => 'minutes', 'value_de' => 'Minuten', 'value_es' => 'minutos'],
            ['translation_key' => 'common.hours', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'hours', 'value_fr' => 'heures', 'value_de' => 'Stunden', 'value_es' => 'horas'],
            ['translation_key' => 'common.days', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'days', 'value_fr' => 'jours', 'value_de' => 'Tage', 'value_es' => 'días'],
            ['translation_key' => 'common.weeks', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'weeks', 'value_fr' => 'semaines', 'value_de' => 'Wochen', 'value_es' => 'semanas'],
            ['translation_key' => 'common.months', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'months', 'value_fr' => 'mois', 'value_de' => 'Monate', 'value_es' => 'meses'],
            ['translation_key' => 'common.years', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'years', 'value_fr' => 'années', 'value_de' => 'Jahre', 'value_es' => 'años'],

            // Status
            ['translation_key' => 'common.active', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Active', 'value_fr' => 'Actif', 'value_de' => 'Aktiv', 'value_es' => 'Activo'],
            ['translation_key' => 'common.inactive', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Inactive', 'value_fr' => 'Inactif', 'value_de' => 'Inaktiv', 'value_es' => 'Inactivo'],
            ['translation_key' => 'common.pending', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Pending', 'value_fr' => 'En attente', 'value_de' => 'Ausstehend', 'value_es' => 'Pendiente'],
            ['translation_key' => 'common.completed', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Completed', 'value_fr' => 'Terminé', 'value_de' => 'Abgeschlossen', 'value_es' => 'Completado'],
            ['translation_key' => 'common.cancelled', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Cancelled', 'value_fr' => 'Annulé', 'value_de' => 'Storniert', 'value_es' => 'Cancelado'],
            ['translation_key' => 'common.confirmed', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Confirmed', 'value_fr' => 'Confirmé', 'value_de' => 'Bestätigt', 'value_es' => 'Confirmado'],
            ['translation_key' => 'common.by', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'by', 'value_fr' => 'par', 'value_de' => 'von', 'value_es' => 'por'],
            ['translation_key' => 'common.total', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'total', 'value_fr' => 'total', 'value_de' => 'gesamt', 'value_es' => 'total'],
            ['translation_key' => 'common.more', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'more', 'value_fr' => 'plus', 'value_de' => 'mehr', 'value_es' => 'más'],
            ['translation_key' => 'common.spots', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'spots', 'value_fr' => 'places', 'value_de' => 'Plätze', 'value_es' => 'plazas'],
            ['translation_key' => 'common.created', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Created', 'value_fr' => 'Créé', 'value_de' => 'Erstellt', 'value_es' => 'Creado'],

            // Table/List
            ['translation_key' => 'common.actions', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Actions', 'value_fr' => 'Actions', 'value_de' => 'Aktionen', 'value_es' => 'Acciones'],
            ['translation_key' => 'common.showing', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Showing', 'value_fr' => 'Affichage', 'value_de' => 'Zeige', 'value_es' => 'Mostrando'],
            ['translation_key' => 'common.of', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'of', 'value_fr' => 'sur', 'value_de' => 'von', 'value_es' => 'de'],
            ['translation_key' => 'common.results', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'results', 'value_fr' => 'résultats', 'value_de' => 'Ergebnisse', 'value_es' => 'resultados'],
            ['translation_key' => 'common.page', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Page', 'value_fr' => 'Page', 'value_de' => 'Seite', 'value_es' => 'Página'],
            ['translation_key' => 'common.per_page', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'per page', 'value_fr' => 'par page', 'value_de' => 'pro Seite', 'value_es' => 'por página'],

            // Misc
            ['translation_key' => 'common.free', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Free', 'value_fr' => 'Gratuit', 'value_de' => 'Kostenlos', 'value_es' => 'Gratis'],
            ['translation_key' => 'common.price', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Price', 'value_fr' => 'Prix', 'value_de' => 'Preis', 'value_es' => 'Precio'],
            ['translation_key' => 'common.description', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Description', 'value_fr' => 'Description', 'value_de' => 'Beschreibung', 'value_es' => 'Descripción'],
            ['translation_key' => 'common.name', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Name', 'value_fr' => 'Nom', 'value_de' => 'Name', 'value_es' => 'Nombre'],
            ['translation_key' => 'common.type', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Type', 'value_fr' => 'Type', 'value_de' => 'Typ', 'value_es' => 'Tipo'],
            ['translation_key' => 'common.status', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Status', 'value_fr' => 'Statut', 'value_de' => 'Status', 'value_es' => 'Estado'],
            ['translation_key' => 'common.date', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Date', 'value_fr' => 'Date', 'value_de' => 'Datum', 'value_es' => 'Fecha'],
            ['translation_key' => 'common.time', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Time', 'value_fr' => 'Heure', 'value_de' => 'Zeit', 'value_es' => 'Hora'],
            ['translation_key' => 'common.yes', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Yes', 'value_fr' => 'Oui', 'value_de' => 'Ja', 'value_es' => 'Sí'],
            ['translation_key' => 'common.no', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'No', 'value_fr' => 'Non', 'value_de' => 'Nein', 'value_es' => 'No'],
            ['translation_key' => 'common.or', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'or', 'value_fr' => 'ou', 'value_de' => 'oder', 'value_es' => 'o'],
            ['translation_key' => 'common.and', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'and', 'value_fr' => 'et', 'value_de' => 'und', 'value_es' => 'y'],
            ['translation_key' => 'common.all', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'All', 'value_fr' => 'Tous', 'value_de' => 'Alle', 'value_es' => 'Todos'],
            ['translation_key' => 'common.none', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'None', 'value_fr' => 'Aucun', 'value_de' => 'Keine', 'value_es' => 'Ninguno'],
            ['translation_key' => 'common.select', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Select', 'value_fr' => 'Sélectionner', 'value_de' => 'Auswählen', 'value_es' => 'Seleccionar'],
            ['translation_key' => 'common.optional', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Optional', 'value_fr' => 'Optionnel', 'value_de' => 'Optional', 'value_es' => 'Opcional'],
            ['translation_key' => 'common.required', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Required', 'value_fr' => 'Requis', 'value_de' => 'Erforderlich', 'value_es' => 'Requerido'],

            // Catalog-related common
            ['translation_key' => 'common.max', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'max', 'value_fr' => 'max', 'value_de' => 'max', 'value_es' => 'máx'],
            ['translation_key' => 'common.instructor', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'instructor', 'value_fr' => 'instructeur', 'value_de' => 'Trainer', 'value_es' => 'instructor'],
            ['translation_key' => 'common.credits', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'credits', 'value_fr' => 'crédits', 'value_de' => 'Guthaben', 'value_es' => 'créditos'],
            ['translation_key' => 'common.credits_per_cycle', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'credits/cycle', 'value_fr' => 'crédits/cycle', 'value_de' => 'Guthaben/Zyklus', 'value_es' => 'créditos/ciclo'],
            ['translation_key' => 'common.class', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'class', 'value_fr' => 'cours', 'value_de' => 'Kurs', 'value_es' => 'clase'],
            ['translation_key' => 'common.slot', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'slot', 'value_fr' => 'créneau', 'value_de' => 'Slot', 'value_es' => 'horario'],
            ['translation_key' => 'common.es', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'es', 'value_fr' => '', 'value_de' => 'e', 'value_es' => 's'],
            ['translation_key' => 'common.all_classes', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'All Classes', 'value_fr' => 'Tous les cours', 'value_de' => 'Alle Kurse', 'value_es' => 'Todas las clases'],
            ['translation_key' => 'common.class_plans', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'class plan(s)', 'value_fr' => 'plan(s) de cours', 'value_de' => 'Kursplan(e)', 'value_es' => 'plan(es) de clase'],

            // Confirmation messages
            ['translation_key' => 'common.action_cannot_undone', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'This action cannot be undone.', 'value_fr' => 'Cette action est irréversible.', 'value_de' => 'Diese Aktion kann nicht rückgängig gemacht werden.', 'value_es' => 'Esta acción no se puede deshacer.'],
            ['translation_key' => 'common.confirm_delete', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Are you sure you want to delete', 'value_fr' => 'Êtes-vous sûr de vouloir supprimer', 'value_de' => 'Sind Sie sicher, dass Sie löschen möchten', 'value_es' => '¿Estás seguro de que quieres eliminar'],
            ['translation_key' => 'common.list', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'List View', 'value_fr' => 'Vue liste', 'value_de' => 'Listenansicht', 'value_es' => 'Vista de lista'],
            ['translation_key' => 'common.grid', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Grid View', 'value_fr' => 'Vue grille', 'value_de' => 'Rasteransicht', 'value_es' => 'Vista de cuadrícula'],
            ['translation_key' => 'common.tbd', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'TBD', 'value_fr' => 'À définir', 'value_de' => 'TBD', 'value_es' => 'Por definir'],
            ['translation_key' => 'common.full', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Full', 'value_fr' => 'Complet', 'value_de' => 'Voll', 'value_es' => 'Lleno'],
            ['translation_key' => 'common.search', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Search', 'value_fr' => 'Rechercher', 'value_de' => 'Suchen', 'value_es' => 'Buscar'],
            ['translation_key' => 'common.unknown', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Unknown', 'value_fr' => 'Inconnu', 'value_de' => 'Unbekannt', 'value_es' => 'Desconocido'],
            ['translation_key' => 'common.deleted', 'category' => 'general_content', 'page_context' => null,
             'value_en' => 'Deleted', 'value_fr' => 'Supprimé', 'value_de' => 'Gelöscht', 'value_es' => 'Eliminado'],
        ];
    }

    /**
     * Catalog page translations (Class Plans, Service Plans, Memberships).
     */
    protected function getCatalogTranslations(): array
    {
        return [
            // Page titles and descriptions
            ['translation_key' => 'catalog.description', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Manage your class templates and service offerings.', 'value_fr' => 'Gérez vos modèles de cours et offres de services.', 'value_de' => 'Verwalten Sie Ihre Kursvorlagen und Dienstleistungen.', 'value_es' => 'Gestiona tus plantillas de clases y ofertas de servicios.'],

            // Class Plans
            ['translation_key' => 'catalog.class_plan', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'class plan', 'value_fr' => 'plan de cours', 'value_de' => 'Kursplan', 'value_es' => 'plan de clase'],
            ['translation_key' => 'catalog.no_class_plans', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'No Class Plans Yet', 'value_fr' => 'Pas encore de plans de cours', 'value_de' => 'Noch keine Kurspläne', 'value_es' => 'No hay planes de clase todavía'],
            ['translation_key' => 'catalog.no_class_plans_desc', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Create your first class plan template to start scheduling classes.', 'value_fr' => 'Créez votre premier modèle de cours pour commencer à planifier des cours.', 'value_de' => 'Erstellen Sie Ihre erste Kursvorlage, um mit der Planung zu beginnen.', 'value_es' => 'Crea tu primera plantilla de clase para empezar a programar clases.'],
            ['translation_key' => 'catalog.create_first_class', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Create First Class Plan', 'value_fr' => 'Créer le premier plan de cours', 'value_de' => 'Ersten Kursplan erstellen', 'value_es' => 'Crear primer plan de clase'],

            // Service Plans
            ['translation_key' => 'catalog.service_plan', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'service plan', 'value_fr' => 'plan de service', 'value_de' => 'Dienstleistungsplan', 'value_es' => 'plan de servicio'],
            ['translation_key' => 'catalog.no_service_plans', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'No Service Plans Yet', 'value_fr' => 'Pas encore de plans de service', 'value_de' => 'Noch keine Dienstleistungspläne', 'value_es' => 'No hay planes de servicio todavía'],
            ['translation_key' => 'catalog.no_service_plans_desc', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Create your first service plan for private sessions or consultations.', 'value_fr' => 'Créez votre premier plan de service pour des sessions privées ou consultations.', 'value_de' => 'Erstellen Sie Ihren ersten Serviceplan für Einzelsitzungen oder Beratungen.', 'value_es' => 'Crea tu primer plan de servicio para sesiones privadas o consultas.'],
            ['translation_key' => 'catalog.create_first_service', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Create First Service Plan', 'value_fr' => 'Créer le premier plan de service', 'value_de' => 'Ersten Serviceplan erstellen', 'value_es' => 'Crear primer plan de servicio'],

            // Membership Plans
            ['translation_key' => 'catalog.membership_plan', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'membership plan', 'value_fr' => 'plan d\'adhésion', 'value_de' => 'Mitgliedschaftsplan', 'value_es' => 'plan de membresía'],
            ['translation_key' => 'catalog.no_membership_plans', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'No Membership Plans Yet', 'value_fr' => 'Pas encore de plans d\'adhésion', 'value_de' => 'Noch keine Mitgliedschaftspläne', 'value_es' => 'No hay planes de membresía todavía'],
            ['translation_key' => 'catalog.no_membership_plans_desc', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Create membership plans to offer recurring access to your classes.', 'value_fr' => 'Créez des plans d\'adhésion pour offrir un accès récurrent à vos cours.', 'value_de' => 'Erstellen Sie Mitgliedschaftspläne für wiederkehrenden Zugang zu Ihren Kursen.', 'value_es' => 'Crea planes de membresía para ofrecer acceso recurrente a tus clases.'],
            ['translation_key' => 'catalog.create_first_membership', 'category' => 'general_content', 'page_context' => 'catalog',
             'value_en' => 'Create First Membership', 'value_fr' => 'Créer la première adhésion', 'value_de' => 'Erste Mitgliedschaft erstellen', 'value_es' => 'Crear primera membresía'],
        ];
    }
}
