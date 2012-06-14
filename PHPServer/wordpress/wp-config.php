<?php
/**
 * The base configurations of the WordPress.
  *
   * This file has the following configurations: MySQL settings, Table Prefix,
    * Secret Keys, WordPress Language, and ABSPATH. You can find more information
     * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
      * wp-config.php} Codex page. You can get the MySQL settings from your web host.
       *
        * This file is used by the wp-config.php creation script during the
	 * installation. You don't have to use the web site, you can just copy this file
	  * to "wp-config.php" and fill in the values.
	   *
	    * @package WordPress
	     */

	     // ** MySQL settings - You can get this info from your web host ** //
	     /** The name of the database for WordPress */
	     define('DB_NAME', 'alfred_doc');

	     /** MySQL database username */
	     define('DB_USER', 'alfred_wp');

	     /** MySQL database password */
	     define('DB_PASSWORD', 'my_cocaine');

	     /** MySQL hostname */
	     define('DB_HOST', 'localhost');

	     /** Database Charset to use in creating database tables. */
	     define('DB_CHARSET', 'utf8');

	     /** The Database Collate type. Don't change this if in doubt. */
	     define('DB_COLLATE', '');

	     /**#@+
	      * Authentication Unique Keys and Salts.
	       *
	        * Change these to different unique phrases!
		 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
		  * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
		   *
		    * @since 2.6.0
		     */
		     define('AUTH_KEY',         'E@(hdRREhSwDN[fO-I&)$px#M7)- cDVi-dh Dd+@6~J*`a$l1u^{?nU5QW>/0|o');
		     define('SECURE_AUTH_KEY',  '|+q|a3u_Od4KK+8OksQi)5bb<kl*Rw|-}NH7S=U>bd*./5}exXo[)ZfdvtM|Q7tr');
		     define('LOGGED_IN_KEY',    'bU]g`FvD@+Idfqo{5N?=K*^^osGTu%/:izg}eY>2+^)Ryh?=)PX#7G5eB=,!h$h|');
		     define('NONCE_KEY',        '>kisJ+xM4#9sBJMNp5^0Df$,`:AB=?:>KTf%r?MUTNc*3L#gi>?@-GkKhW_B.qV.');
		     define('AUTH_SALT',        'V#UZ=&4+<4t$y]kX^E/Y/@w]:Jl~BP36, X/3l{I_n++]@/G`{;ybo ^ftZ=1%k+');
		     define('SECURE_AUTH_SALT', '8D6q??r9qRft+,Q3M-heZ&$cL[8&7U.sw[K!ufp5ADrZU9[~A$9&* EX2$z3RCNS');
		     define('LOGGED_IN_SALT',   '`Lu+Q.(Y@2yiPlJ9+8X*_f&Bf,h `X.>t&cv[D*]OagP93TQvEa7}VUq`$,Xp~Nh');
		     define('NONCE_SALT',       'E:w$Y-MOhfy+]Z@6?Cc:B2d3>sY?S^cBI/M:|ODa[go=LE)Ly8Q2Z&{T9x,;Y4Ms');

		     /**#@-*/

		     /**
		      * WordPress Database Table prefix.
		       *
		        * You can have multiple installations in one database if you give each a unique
			 * prefix. Only numbers, letters, and underscores please!
			  */
			  $table_prefix  = 'alfred_wp_';

			  /**
			   * WordPress Localized Language, defaults to English.
			    *
			     * Change this to localize WordPress. A corresponding MO file for the chosen
			      * language must be installed to wp-content/languages. For example, install
			       * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
			        * language support.
				 */
				 define('WPLANG', '');

				 /**
				  * For developers: WordPress debugging mode.
				   *
				    * Change this to true to enable the display of notices during development.
				     * It is strongly recommended that plugin and theme developers use WP_DEBUG
				      * in their development environments.
				       */
				       define('WP_DEBUG', false);

				       /* That's all, stop editing! Happy blogging. */

				       /** Absolute path to the WordPress directory. */
				       if ( !defined('ABSPATH') )
				       	define('ABSPATH', dirname(__FILE__) . '/');

					/** Sets up WordPress vars and included files. */
					require_once(ABSPATH . 'wp-settings.php');

