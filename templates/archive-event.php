<div class="events-header">
  <h1><?php the_archive_title(); ?></h1>
</div>

<?php if (!have_posts()) : ?>
  There are currently no upcoming events.
<?php endif; ?>

<div class="events">
  <?php while (have_posts()) : the_post(); ?>
    <?php $event_fields = get_fields(); ?>
    <article <?php post_class(); ?>>
      <header>
        <?php the_post_thumbnail(); ?>
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php include('event-meta.php'); ?>
      </header>
      <div class="entry-summary">
        <?php the_excerpt(); ?>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<?php the_posts_pagination(); ?>
