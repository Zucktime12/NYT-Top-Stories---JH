<?php
/**
 * Template Name: API Info Template
 *
 * Template for displaying a blank page.
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id='site-content' role="main">
	<!-- <table>
		<tr>
			<td><b>Title</b></td>
			<td><b>Post Excerpt</b></td>
			<td><b>Post Date</b></td>
			<td><b>URL</b></td>
			<td><b>Byline</b></td>
			<td><b>Category</b></td>
			<td><b>Tags</b></td>
		</tr> -->
	
	<?php 
		global $wpdb;

		$table_name = $wpdb->prefix . 'jh_nyt_table_version';

		$sql = "SELECT * FROM $table_name";

		$table_info = $wpdb->get_results($sql);

		$i = 0;

		foreach($table_info as $info_row ) {
			?>
			<ul>
				<li><b><a href="<?php echo $info_row->urlStory; ?>" target="_blank"><?php echo $info_row->title; ?></a></b></li>
				<li><?php echo $info_row->abstract; ?></li>
				<li><?php echo $info_row->published_date; ?></li>
				<!-- leaving in the link but also adding in the title linking to the story -->
				<li><a href="<?php echo $info_row->urlStory; ?>" target="_blank"><?php echo $info_row->urlStory; ?></a></li>
				<li><?php echo $info_row->byline; ?></li>
				<li><?php echo $info_row->section; ?></li>
				<li><?php echo $info_row->des_facet; ?></li>
			</ul>
			<?php
			$i++;
			
			if($i>4) {
				break;
			}
		}

		// test output table_info to front end 
		// echo '<pre>';
		// print_r($table_info);
		// echo '</pre>';

		// print_r(get_option('jh_nyt_top_stories'));

	
	?>
	<!-- </table> -->
</main>

<?php 
get_footer();