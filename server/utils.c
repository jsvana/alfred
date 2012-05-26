#include <glib.h>

char *alfred_utils_md5(char *data) {
	return g_compute_checksum_for_string(G_CHECKSUM_MD5, data, -1);
}
