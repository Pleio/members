<?php
/**
 * Members index
 *
 */

global $CONFIG;

$site = elgg_get_site_entity();

$title = elgg_echo('members');

switch ($vars['page']) {
	case 'online':
		$content = get_online_users();
		break;
	case 'newest':
		if (class_exists("ESInterface")) {
			$result = ESInterface::get()->search("", SEARCH_DEFAULT, "user", [], get_input("limit"), get_input("offset"), "time_created", "desc");

			$num_members = $result["count"];
			$content = elgg_view_entity_list($result["hits"], [
				"count" => $result["count"],
				"offset" => get_input("offset") ? get_input("offset") : 0,
				"limit" => get_input("limit") ? get_input("limit") : 10,
				"pagination" => true
			]);
		} else {
			$content = elgg_list_entities_from_relationship($options);
		}

		break;
	case 'alpha':
	default:
		if (class_exists("ESInterface")) {
			$result = ESInterface::get()->search("", SEARCH_DEFAULT, "user", [], get_input("limit"), get_input("offset"), "name", "asc");

			$num_members = $result["count"];
			$content = elgg_view_entity_list($result["hits"], [
				"count" => $result["count"],
				"offset" => get_input("offset") ? get_input("offset") : 0,
				"limit" => get_input("limit") ? get_input("limit") : 10,
				"pagination" => true
			]);
		} else {
			$options["joins"] = array("INNER JOIN {$CONFIG->dbprefix}users_entity o ON (e.guid = o.guid)");
			$options["order_by"] = "o.name";
			$content = elgg_list_entities_from_relationship($options);
		}

		break;
}

if (!class_exists("ESInterface")) {
	$options = array(
		'type' => 'user',
		'site_guids' => false,
		'relationship' => 'member_of_site',
		'relationship_guid' => $site->getGUID(),
		'inverse_relationship' => true,
		'full_view' => false,
		'count' => true
	);


	$num_members = elgg_get_entities_from_relationship($options);
}

if(empty($content)) {
	$content = elgg_echo("notfound");
}


$params = array(
	'content' => $content,
	'sidebar' => elgg_view('members/sidebar'),
	'title' => $title . " ($num_members)",
	'filter_override' => elgg_view('members/nav', array('selected' => $vars['page'])),
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
