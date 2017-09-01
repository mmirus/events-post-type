<?php while (have_posts()) : the_post(); ?>
  <?php $event_fields = get_fields(); ?>
  
  <article <?php post_class(); ?>>
    <header>
      <?php the_post_thumbnail(); ?>
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <?php include('event-meta.php'); ?>
    </header>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </article>
<?php endwhile; ?>
