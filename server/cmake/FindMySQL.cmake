# - Find MySQL
# Find the MySQL includes and client library
# This module defines
#  MYSQL_INCLUDE_DIR, where to find mysql.h
#  MYSQL_LIBRARIES, the libraries needed to use MySQL.
#  MYSQL_CFLAGS, the cflags needed to use MySQL
#  MYSQL_FOUND, If false, do not try to use MySQL.

# Copyright (c) 2006, Jaroslaw Staniek, <js@iidea.pl>
# Copyright (c) 2012, Kaleb Elwert <kelwert@mtu.edu>
#
# Redistribution and use is allowed according to the terms of the BSD license.
# For details see the accompanying COPYING-CMAKE-SCRIPTS file.

# Note: While mysql_config is probably a little more "correct", the output from find_package_handle_standard_args is prettier this way

#find_program(MYSQLCONFIG_EXECUTABLE NAMES mysql_config mysql_config5 PATHS ${BIN_INSTALL_DIR} ~/usr/bin /usr/local/bin)

#if(MYSQLCONFIG_EXECUTABLE)
#    exec_program(${MYSQLCONFIG_EXECUTABLE} ARGS --include RETURN_VALUE _return_VALUE OUTPUT_VARIABLE MYSQL_INCLUDE_DIR)
#    exec_program(${MYSQLCONFIG_EXECUTABLE} ARGS --cflags RETURN_VALUE _return_VALUE OUTPUT_VARIABLE MYSQL_CFLAGS)
#    exec_program(${MYSQLCONFIG_EXECUTABLE} ARGS --libs RETURN_VALUE _return_VALUE OUTPUT_VARIABLE MYSQL_LIBRARIES)
#else(MYSQLCONFIG_EXECUTABLE)

    find_path(MYSQL_INCLUDE_DIR mysql.h
       ~/usr/include/mysql
       /opt/local/include/mysql5/mysql
       /opt/mysqle/include/mysql
       /opt/mysql/mysql/include 
       /usr/mysql/include/mysql
       /usr/include/mysql
       /usr/local/include/mysql
       /opt/local/include/mysql
       /opt/ports/include/mysql5/mysql
    )

	set(MYSQL_CLIENT_LIBRARY_NAME mysqlclient)

    find_library(MYSQL_LIBRARIES NAMES ${MYSQL_CLIENT_LIBRARY_NAME}
      PATHS
        ~/usr/lib/mysql
        /opt/mysql/mysql/lib 
        usr/mysql/lib/mysql
        opt/local/lib/mysql5/mysql
        opt/mysqle/lib/mysql
        usr/lib/mysql
        usr/lib64/mysql
        usr/lib64
        usr/local/lib/mysql
        opt/local/lib/mysql
        opt/ports/lib/mysql5/mysql
    )
#endif(MYSQLCONFIG_EXECUTABLE)

include(FindPackageHandleStandardArgs)
find_package_handle_standard_args(MySQL DEFAULT_MSG MYSQL_LIBRARIES MYSQL_INCLUDE_DIR)
mark_as_advanced(MYSQL_INCLUDE_DIR MYSQL_LIBRARIES MYSQL_CFLAGS)
