--- a/lib/ext2fs/ext2_fs.h.orig	Sat Jun 30 16:36:37 2007
+++ b/lib/ext2fs/ext2_fs.h	Sat Jun 30 16:36:43 2007
@@ -421,7 +421,7 @@
 
 #define i_size_high	i_dir_acl
 
-#if defined(__KERNEL__) || defined(__linux__)
+#if defined(__KERNEL__) || defined(__linux__) || defined(__FreeBSD__)
 #define i_reserved1	osd1.linux1.l_i_reserved1
 #define i_frag		osd2.linux2.l_i_frag
 #define i_fsize		osd2.linux2.l_i_fsize
