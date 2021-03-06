PORTNAME=	proftpd
PORTVERSION=	1.3.3g
CATEGORIES=	ftp
COMMENT=	Highly configurable ftp daemon
MAINTAINER=	aoyama@peach.ne.jp

MASTER_SITES=	ftp://ftp.proftpd.org/distrib/source/ \
		ftp://ftp.fastorama.com/mirrors/ftp.proftpd.org/distrib/source/ \
		ftp://ftp.dataguard.no/ftp/pub/proftpd/distrib/source/ \
		ftp://spirit.bentel.sk/mirrors/Proftpd/distrib/source/ \
		ftp://proftpd.networkedsystems.co.uk/distrib/source/ \
		http://mirrors.ibiblio.org/pub/mirrors/proftpd/distrib/source/ \
		http://www.mirrorservice.org/sites/ftp.proftpd.org/distrib/source/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX}
EXTRACT_ONLY=	${DISTNAME}${EXTRACT_SUFX}

USE_GMAKE=	yes
USE_BZIP2=	yes
USE_AUTOTOOLS=	autoconf
GNU_CONFIGURE=	yes
USE_GETTEXT=	yes
USE_OPENSSL=	yes

MODULES=	mod_ratio:mod_readme:mod_rewrite:mod_site_misc:mod_tls:mod_wrap:mod_wrap2:mod_wrap2_file:mod_ban:mod_ctrls_admin:mod_ifsession
OPTIONS+=       WRAP "Include mod_wrap2" on

CONFIGURE_ARGS=	--localstatedir=/var/run \
		--with-pkgconfig=libdata/pkgconfig \
		--sysconfdir=${PREFIX}/etc \
		--enable-dso \
		--disable-sendfile \
		--enable-ipv6 \
		--enable-nls \
		--with-modules=${MODULES} \
		--with-includes=${WORKDIR}/include:${LOCALBASE}/include \
		--with-libraries=${LOCALBASE}/lib \
		--enable-auth-pam \
		--enable-ctrls \
		--enable-largefile
CONFIGURE_ENV=	"LIBS=-lintl -L${LOCALBASE}/lib -lssl -lcrypto -L${OPENSSLLIB}"
CFLAGS+=	-DHAVE_OPENSSL -I${OPENSSLINC}

.include <bsd.port.pre.mk>

# FreeBSD-SA-11:07.chroot
EXTRA_PATCHES+=	${FILESDIR}/extra-patch-8-src-fsio.c

MODULES!=${ECHO} ${MODULES} | ${SED} -e 's,^:,,' -e 's,:$$,,'

post-patch:
	@${REINPLACE_CMD} -e 's|-lsupp|-lsupp -lintl|g' ${WRKSRC}/Makefile.in
	@${REINPLACE_CMD} -e 's|proftpd\.conf|proftpd.conf.sample|g' ${WRKSRC}/Makefile.in

pre-configure:
	@${ECHO_MSG} "==> Configuring with ${MODULES}"

post-configure:
	${REINPLACE_CMD} -e 's: -lnsl::' ${WRKSRC}/Make.rules
	${REINPLACE_CMD} -e 's:/usr/sbin:${PREFIX}/sbin:' \
		-e 's:/usr/bin:${PREFIX}/bin:' \
		${WRKSRC}/src/proftpd.8 ${WRKSRC}/utils/ftpshut.8 \
		${WRKSRC}/utils/ftpcount.1

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${FREENAS_ROOTFS}/etc/rc.d/${PORTNAME}

	@${INSTALL_PROGRAM} -v ${WRKSRC}/ftpwho ${FREENAS_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ftptop ${FREENAS_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ftpdctl ${FREENAS_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME} ${FREENAS_ROOTFS}/${PREFIX}/sbin

.include <bsd.port.post.mk>
