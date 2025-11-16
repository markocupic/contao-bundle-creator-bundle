<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Yaml\Yaml;

class Configuration implements ConfigurationInterface
{
    public const ROOT_KEY = 'markocupic_contao_bundle_creator';

    public function __construct(private readonly string $projectDir)
    {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_KEY);

        $treeBuilder->getRootNode()
            ->children()
                // Language
                ->scalarNode('defaultSkeletonPath')->info('Set the default language.')->defaultValue('en')->cannotBeEmpty()->end()
                // Section name
                ->scalarNode('section_name')->info('e.g. SAC Sektion Pilatus')->cannotBeEmpty()->end()
                // Member database sync Zentralverband Bern
                ->arrayNode('member_sync_credentials')
                    ->children()
                        ->scalarNode('hostname')->cannotBeEmpty()->end()
                        ->scalarNode('username')->cannotBeEmpty()->end()
                        ->scalarNode('password')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                // Event admin name
                ->scalarNode('event_admin_name')->cannotBeEmpty()->end()
                // Event admin email
                ->scalarNode('event_admin_email')->cannotBeEmpty()->end()
                // Temp dir e.g system/tmp
                ->scalarNode('temp_dir')->defaultValue('system/tmp')->cannotBeEmpty()->end()
                // Avatars
                ->arrayNode('avatar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('female')->defaultValue('vendor/markocupic/sac-event-tool-bundle/public/images/avatars/avatar-default-female.png')->end()
                        ->scalarNode('male')->defaultValue('vendor/markocupic/sac-event-tool-bundle/public/images/avatars/avatar-default-male.png')->end()
                        ->scalarNode('other')->defaultValue('vendor/markocupic/sac-event-tool-bundle/public/images/avatars/avatar-default-other.png')->end()
                    ->end()
                ->end()
                // Backend and frontend users
                ->arrayNode('user')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('backend')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('home_dir')->defaultValue('files/sektion/be_user_home_directories')->end()
                                ->booleanNode('reset_permissions_on_login')->defaultFalse()->end()
                                ->arrayNode('rescission_cause')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['accident', 'deceased', 'recission', 'leaving', 'pausing', 'another'])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('frontend')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('home_dir')->defaultValue('files/sektion/fe_user_home_directories')->end()
                            ->scalarNode('avatar_dir')->defaultValue('files/sektion/fe_user_home_directories/avatars')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                // Events
                ->arrayNode('event')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('duration_info')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                    ->normalizeKeys(false)
                                    ->defaultValue(Yaml::parse(file_get_contents($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/config/defaults/event_duration_opt.yaml')))
                                ->end()
                                ->arrayNode('avalanche_level')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['avalanche_level_0', 'avalanche_level_1', 'avalanche_level_2', 'avalanche_level_3', 'avalanche_level_4', 'avalanche_level_5'])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('course')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('levels')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                    ->normalizeKeys(false)
                                    ->defaultValue(Yaml::parse(file_get_contents($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/config/defaults/course_level_opt.yaml')))
                                ->end()
                                ->scalarNode('booklet_cover_image')->defaultValue('vendor/markocupic/sac-event-tool-bundle/public/images/events/course/booklet/cover.jpg')->end()
                                ->scalarNode('booklet_filename_pattern')->defaultValue('Kursprogramm_%%s.pdf')->end()
                                ->scalarNode('fallback_image')->defaultValue('vendor/markocupic/sac-event-tool-bundle/public/images/events/course/fallback_image.svg')->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                // Event member list docx template
                                ->scalarNode('member_list')->defaultValue('vendor/markocupic/sac-event-tool-bundle/contao/templates/docx/event_memberlist.docx')->end()
                                // Event tour invoice docx template
                                ->scalarNode('tour_invoice')->defaultValue('vendor/markocupic/sac-event-tool-bundle/contao/templates/docx/event_invoice_tour.docx')->end()
                                // Event tour rapport docx template
                                ->scalarNode('tour_rapport')->defaultValue('vendor/markocupic/sac-event-tool-bundle/contao/templates/docx/event_rapport_tour.docx')->end()
                                // Event course confirmation docx template
                                ->scalarNode('course_confirmation')->defaultValue('vendor/markocupic/sac-event-tool-bundle/contao/templates/docx/course_confirmation.docx')->end()
                            ->end()
                        ->end()
                        // Member list file name pattern
                        ->scalarNode('member_list_file_name_pattern')->defaultValue('SAC_Event_Teilnehmerliste_%%s.%%s')->end()
                        // Event tour invoice file name pattern
                        ->scalarNode('tour_invoice_file_name_pattern')->defaultValue('SAC_Event_Verguetungsformular_%%s.%%s')->end()
                        // Event tour rapport file name pattern
                        ->scalarNode('tour_rapport_file_name_pattern')->defaultValue('SAC_Event_Tourrapport_%%s.%%s')->end()
                        // Event course confirmation file name pattern
                        ->scalarNode('course_confirmation_file_name_pattern')->defaultValue('SAC_Event_Kursbestaetigung_%%s_regId_%%s.%%s')->end()
                        // Coordinates
                        ->scalarNode('geo_link')
                            ->cannotBeEmpty()
                            // The coord "%s" placeholders have to be escaped by an additional percent char
                            // => %%s
                            ->defaultValue('https://map.geo.admin.ch/embed.html?lang=de&topic=ech&bgLayer=ch.swisstopo.pixelkarte-farbe&layers=ch.bav.haltestellen-oev,ch.swisstopo.swisstlm3d-wanderwege,ch.swisstopo-karto.skitouren,ch.astra.wanderland-sperrungen_umleitungen&E=%%s&N=%%s&zoom=6&crosshair=marker')
                        ->end()
                        // SAC Route Portal Base Link
                        ->scalarNode('sac_route_portal_base_link')
                            ->cannotBeEmpty()
                            ->defaultValue('https://www.sac-cas.ch/de/huetten-und-touren/sac-tourenportal/')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('event_registration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('email_accept_templ_path')
                                    ->defaultValue($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/templates/Email/EventRegistration/email_reg_accept.twig')
                                ->end()
                                ->scalarNode('email_cancel_templ_path')
                                    ->defaultValue($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/templates/Email/EventRegistration/email_reg_cancel.twig')
                                ->end()
                                ->scalarNode('email_refuse_templ_path')
                                    ->defaultValue($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/templates/Email/EventRegistration/email_reg_refuse.twig')
                                ->end()
                                ->scalarNode('email_waitinglist_templ_path')
                                    ->defaultValue($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/templates/Email/EventRegistration/email_reg_waitinglist.twig')
                                ->end()
                                // Custom email text for accepting registrations
                                ->scalarNode('email_accept_custom_templ_path')
                                    ->cannotBeEmpty()
                                    ->info('The instructor can send a customized email for accepting registrations in the Contao backend.')
                                    ->defaultValue($this->projectDir.'/vendor/markocupic/sac-event-tool-bundle/templates/Email/EventRegistration/email_reg_accept_personalized.txt')
                                ->end()
                                ->arrayNode('car_seat_info')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['kein Auto', '2', '3', '4', '5', '6', '7', '8', '9'])
                                ->end()
                                ->arrayNode('ticket_info')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['Nichts', 'GA', 'Halbtax-Abo'])
                                ->end()
                                ->integerNode('reg_start_time_offset')
                                    ->info('Number of seconds to be added to tl_calendar_events-registrationStartTime to calculate the exact time from which registrations should be possible.')
                                    ->defaultValue(6 * 60 * 60) // 6h
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
