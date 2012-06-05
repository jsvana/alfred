# - Find Glib-2.0
# This module finds Glib as well as the
# optional GModule and GThread libraries
#
# The following add-on modules can be included.
#   gio
#   gio-unix
#   gmodule
#   gmodule-export
#   gmodule-no-export
#   gobject
#   gthread
#
# When done, this will define:
#  GLIB_FOUND - system has GLIB
#  GLIB_INCLUDE_DIR - the GLIB include directories
#  GLIB_LIBRARIES - link these to use GLIB
#  GLIB_VERSION_STRING - The version of glib
#  GLIB_MAJOR_VERSION - Major version component
#  GLIB_MINOR_VERSION - Minor version component
#  GLIB_MICRO_VERSION - Micro version component
#
# In addition to the regular variables, the following
# will be defined for each component:
#  GLIB_component_FOUND - System has "component"
#  GLIB_component_LIBRARY - Libraries to link to this component

#=============================================================================
# Copyright 2012 Kaleb Elwert <kelwert@mtu.edu>
#
# Distributed under the OSI-approved BSD License (the "License");
# see accompanying file Copyright.txt for details.
#
# This software is distributed WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the License for more information.
#=============================================================================
# (To distribute this file outside of CMake, substitute the full
#  License text for the above reference.)

include(FindPkgConfig)
include(FindPackageHandleStandardArgs)

pkg_check_modules(GLIB_PC QUIET glib-2.0)

# GLIB-related libraries also use a separate config header, which is in lib dir
find_path(GLIB_CONFIG_INCLUDE_DIR
	NAMES glibconfig.h
	HINTS
		${GLIB_PC_INCLUDE_DIRS}
	PATH_SUFFIXES
		glib-2.0
		glib-2.0/include
)

# Main include dir
find_path(GLIB_LIB_INCLUDE_DIR
	NAMES glib.h
	HINTS
		${GLIB_PC_INCLUDE_DIRS}
	PATH_SUFFIXES
		glib-2.0
		glib-2.0/include
)

# Version Check
if(GLIB_PC_VERSION)
	set(GLIB_VERSION_STRING ${GLIB_PC_VERSION})
	string(REGEX REPLACE "([0-9]+).*" "\\1" GLIB_MAJOR_VERSION "${GLIB_VERSION_STRING}")
	string(REGEX REPLACE "[0-9]+\\.([0-9]+).*" "\\1" GLIB_MINOR_VERSION "${GLIB_VERSION_STRING}")
	string(REGEX REPLACE "[0-9]+\\.[0-9]+\\.([0-9]+).*" "\\1" GLIB_MICRO_VERSION "${GLIB_VERSION_STRING}")
else()
	if(GLIB_CONFIG_INCLUDE_DIR AND EXISTS "${GLIB_CONFIG_INCLUDE_DIR}/glibconfig.h")
		# Find the version components
		file(READ "${GLIB_CONFIG_INCLUDE_DIR}/glibconfig.h" _contents)
		string(REGEX REPLACE ".*#define GLIB_MAJOR_VERSION[ \t]+([0-9]+).*" "\\1" GLIB_MAJOR_VERSION "${_contents}")
		string(REGEX REPLACE ".*#define GLIB_MINOR_VERSION[ \t]+([0-9]+).*" "\\1" GLIB_MINOR_VERSION "${_contents}")
		string(REGEX REPLACE ".*#define GLIB_MICRO_VERSION[ \t]+([0-9]+).*" "\\1" GLIB_MICRO_VERSION "${_contents}")
		set(GLIB_VERSION_STRING ${GLIB_MAJOR_VERSION}.${GLIB_MINOR_VERSION}.${GLIB_MICRO_VERSION})
	endif()
endif()

# Finally the libraries
find_library(GLIB_LIBRARY
	NAMES glib-2.0
	HINTS
	${GLIB_PC_LIBRARY_DIRS}
)

set(GLIB_REQUIRED_VARS GLIB_LIBRARY GLIB_LIB_INCLUDE_DIR GLIB_CONFIG_INCLUDE_DIR)

if(Glib_FIND_COMPONENTS)
	foreach(component ${Glib_FIND_COMPONENTS})
		if("${component}" STREQUAL "gio" OR
			"${component}" STREQUAL "gio-unix" OR
			"${component}" STREQUAL "gmodule" OR
			"${component}" STREQUAL "gmodule-export" OR
			"${component}" STREQUAL "gmodule-no-export" OR
			"${component}" STREQUAL "gobject" OR
			"${component}" STREQUAL "gthread")
			pkg_check_modules(GLIB_${component}_PC QUIET ${component}-2.0)
			find_library(GLIB_${component}_LIBRARY
				NAMES ${component}-2.0
				HINTS
					${GLIB_${component}_PC_LIBRARY_DIRS}
			)
			if(GLIB_${component}_LIBRARY)
				set(GLIB_${component}_FOUND true)
			endif()
			if(GLIB_FIND_REQUIRED_${component})
				list(APPEND GLIB_REQUIRED_VARS GLIB_${component}_LIBRARY)
			endif()
			mark_as_advanced(GLIB_${component}_LIBRARY)
		else()
			message(FATAL_ERROR "Module ${component} was not found")
		endif()
	endforeach(component ${Glib_FIND_COMPONENTS})
endif()

find_package_handle_standard_args(
	Glib
	REQUIRED_VARS ${GLIB_REQUIRED_VARS}
	#VERSION_VAR GLIB_VERSION_STRING
)

mark_as_advanced(
	GLIB_LIB_INCLUDE_DIR
	GLIB_CONFIG_INCLUDE_DIR
	GLIB_LIBRARY
)

if(GLIB_FOUND)
	set(GLIB_LIBRARIES
		${GLIB_LIBRARY}
		${GLIB_gio_LIBRARY}
		${GLIB_gio-unix_LIBRARY}
		${GLIB_gmodule_LIBRARY}
		${GLIB_gmodule-export_LIBRARY}
		${GLIB_gmodule-no-export_LIBRARY}
		${GLIB_gobject_LIBRARY}
		${GLIB_gthread_LIBRARY}
	)
	set(GLIB_INCLUDE_DIR
		${GLIB_LIB_INCLUDE_DIR}
		${GLIB_CONFIG_INCLUDE_DIR}
	)
	list(REMOVE_DUPLICATES GLIB_LIBRARIES)
	list(REMOVE_DUPLICATES GLIB_INCLUDE_DIR)
endif()
