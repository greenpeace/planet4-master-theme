<?php

/**
 * Give alias with the pre PSR-4 name to all classes.
 *
 * @package P4MT
 * @todo: remove after full namespace deployment to plugins and child themes
 */

class_alias(\P4\MasterTheme\Activator::class, 'P4_Activator');
class_alias(\P4\MasterTheme\AnalyticsValues::class, 'P4_Analytics_Values');
class_alias(\P4\MasterTheme\Exporter::class, 'P4_Campaign_Exporter');
class_alias(\P4\MasterTheme\Importer::class, 'P4_Campaign_Importer');
class_alias(\P4\MasterTheme\Role\Campaigner::class, 'P4_Campaigner');
class_alias(\P4\MasterTheme\Campaigns::class, 'P4_Campaigns');
class_alias(\P4\MasterTheme\Context::class, 'P4_Context');
class_alias(\P4\MasterTheme\ControlPanel::class, 'P4_Control_Panel');
class_alias(\P4\MasterTheme\Cookies::class, 'P4_Cookies');
class_alias(\P4\MasterTheme\CustomTaxonomy::class, 'P4_Custom_Taxonomy');
class_alias(\P4\MasterTheme\DevReport::class, 'P4_Dev_Report');
class_alias(\P4\MasterTheme\ElasticSearch::class, 'P4_ElasticSearch');
class_alias(\P4\MasterTheme\ImageCompression::class, 'P4_Image_Compression');
class_alias(\P4\MasterTheme\Loader::class, 'P4_Loader');
class_alias(\P4\MasterTheme\MetaboxRegister::class, 'P4_Metabox_Register');
class_alias(\P4\MasterTheme\Post::class, 'P4_Post');
class_alias(\P4\MasterTheme\PostArchive::class, 'P4_Post_Archive');
class_alias(\P4\MasterTheme\PostCampaign::class, 'P4_Post_Campaign');
class_alias(\P4\MasterTheme\PostReportController::class, 'P4_Post_Report_Controller');
class_alias(\P4\MasterTheme\Search::class, 'P4_Search');
class_alias(\P4\MasterTheme\Settings::class, 'P4_Settings');
class_alias(\P4\MasterTheme\Sitemap::class, 'P4_Sitemap');
class_alias(\P4\MasterTheme\TaxonomyCampaign::class, 'P4_Taxonomy_Campaign');
class_alias(\P4\MasterTheme\User::class, 'P4_User');

class_alias(\P4\MasterTheme\Settings\Features::class, \P4\MasterTheme\Features::class);
