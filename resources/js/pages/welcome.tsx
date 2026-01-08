import Head from '@/components/head';
import { FeatureCard } from '@/components/ui/FeatureCard';
import { PricingCard } from '@/components/ui/pricing-card';
import { Feature, PageProps, Plan } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ArrowRight, ArrowUp, DollarSign, FileText, LogIn, Menu, Shield, Star, TrendingUp, Users, X, Zap } from 'lucide-react';
import { useEffect, useState } from 'react';
import { FaTelegram } from 'react-icons/fa6';

export default function Welcome() {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [showScrollTop, setShowScrollTop] = useState(false);
    const [isScrolled, setIsScrolled] = useState(false);
    const { auth } = usePage<PageProps>().props;
    const [activeTestimonial, setActiveTestimonial] = useState(0);

    const company = auth?.user?.company;

    const navLinks = [
        { href: '#features', label: 'Fonctionnalités' },
        { href: '#pricing', label: 'Tarifs' },
        { href: '#faq', label: 'FAQ' },
        { href: '#testimonials', label: 'Témoignages' },
    ];

    useEffect(() => {
        if (auth.user) {
            router.visit('/dashboard');
        }
    }, [auth]);

    useEffect(() => {
        const handleScroll = () => {
            setShowScrollTop(window.scrollY > 400);
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    useEffect(() => {
        const handleScroll = () => {
            const scrollPosition = window.scrollY;

            setShowScrollTop(scrollPosition > 400);

            setIsScrolled(scrollPosition > 50);
            setMobileMenuOpen(false);
        };

        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleNavigate = (url: string) => {
        router.visit(url);
    };

    const plans: Plan[] = [
        {
            title: 'Gratuit',
            price: '0',
            description: 'Parfait pour démarrer',
            features: ["Jusqu'à 10 factures/mois", '3 modèles de factures', 'Gestion des clients basique'],
            buttonText: 'Commencer gratuitement',
            buttonVariant: 'default',
            onButtonClick: () => handleNavigate('/company?plan=free'),
        },
        {
            title: 'Premium',
            price: '9 900 FCFA',
            description: 'Pour les entreprises en croissance',
            features: [
                'Factures et devis illimités',
                'Gestion des clients et articles',
                'Relances automatiques par email',
                'Tableau de bord analytique',
                'Support prioritaire 24/7',
            ],
            buttonText: 'Essayer en premium',
            buttonVariant: 'primary',
            badge: 'POPULAIRE',
            popular: true,
            onButtonClick: () => handleNavigate('/company?plan=premium'),
        },
        {
            title: 'Entreprise',
            price: '14 900 FCFA',
            description: 'Pour les grandes structures',
            features: [
                'Utilisateurs illimités',
                'Gestion avancée des stocks',
                'Exports comptables (CSV, Excel)',
                'Branding personnalisé',
                'Account manager dédié',
            ],
            buttonText: 'Essayer en entreprise',
            buttonVariant: 'primary',
            onButtonClick: () => handleNavigate('/company?plan=enterprise'),
        },
    ];

    const features: Feature[] = [
        {
            icon: Zap,
            title: 'Facturation rapide',
            description: 'Créez des factures professionnelles en moins de 2 minutes avec nos modèles personnalisables.',
            iconColor: 'blue',
        },
        {
            icon: Shield,
            title: 'Sécurisé',
            description: 'Vos données sont cryptées et sauvegardées automatiquement. Conformité RGPD garantie.',
            iconColor: 'emerald',
        },
        {
            icon: TrendingUp,
            title: 'Analyses détaillées',
            description: 'Suivez vos revenus, vos paiements en attente et analysez vos performances en temps réel.',
            iconColor: 'purple',
        },
        {
            icon: DollarSign,
            title: 'Paiements simplifiés',
            description: 'Acceptez les paiements en ligne et suivez automatiquement les règlements de vos clients.',
            iconColor: 'orange',
        },
        {
            icon: Users,
            title: 'Gestion clients',
            description: 'Centralisez toutes les informations de vos clients et leur historique de facturation.',
            iconColor: 'pink',
        },
        {
            icon: FileText,
            title: 'Devis & Factures',
            description: 'Convertissez vos devis en factures en un clic et personnalisez tous vos documents.',
            iconColor: 'indigo',
        },
    ];

    const questions = [
        {
            question: "Comment fonctionne l'essai gratuit ?",
            answer: "L'essai gratuit de 14 jours vous donne accès à toutes les fonctionnalités du plan Professional. Aucune carte bancaire n'est requise pour commencer. À la fin de l'essai, vous pouvez choisir de continuer avec un abonnement payant ou revenir au plan gratuit.",
        },
        {
            question: 'Puis-je changer de plan à tout moment ?',
            answer: "Oui, vous pouvez passer d'un plan à un autre à tout moment. Si vous passez à un plan supérieur, les modifications sont immédiates. Si vous rétrogradez, les changements prendront effet à la fin de votre période de facturation actuelle.",
        },
        {
            question: 'Mes données sont-elles sécurisées ?',
            answer: 'Absolument. Nous utilisons un cryptage SSL de niveau bancaire pour toutes les transactions. Vos données sont sauvegardées quotidiennement et stockées sur des serveurs sécurisés. Nous sommes également conformes au RGPD.',
        },
        {
            question: 'Puis-je importer mes factures existantes ?',
            answer: "Oui, nous proposons des outils d'import pour transférer vos données depuis Excel, CSV ou d'autres logiciels de facturation. Notre équipe support peut vous aider dans ce processus de migration.",
        },
        {
            question: 'Comment fonctionne le paiement en ligne ?',
            answer: 'Nous intégrons Stripe et PayPal pour accepter les paiements directement depuis vos factures. Vos clients peuvent payer par carte bancaire ou virement. Les fonds sont transférés directement sur votre compte.',
        },
        {
            question: 'Y a-t-il des frais cachés ?',
            answer: 'Non, nos prix sont totalement transparents. Le montant affiché est celui que vous payez. Les seuls frais supplémentaires potentiels sont les commissions des processeurs de paiement (Stripe/PayPal) si vous utilisez les paiements en ligne.',
        },
        {
            question: 'Puis-je annuler mon abonnement ?',
            answer: "Oui, vous pouvez annuler votre abonnement à tout moment depuis les paramètres de votre compte. Il n'y a aucun engagement et aucune pénalité d'annulation. Vous conserverez l'accès jusqu'à la fin de votre période payée.",
        },
        {
            question: 'Proposez-vous une assistance client ?',
            answer: "Oui ! Le plan gratuit inclut un support par email avec réponse sous 48h. Les plans payants bénéficient d'un support prioritaire (12h pour Professional, 24/7 pour Enterprise) par email, chat et téléphone.",
        },
    ];

    useEffect(() => {
        const interval = setInterval(() => {
            setActiveTestimonial((prev) => (prev + 1) % 3);
        }, 5000);
        return () => clearInterval(interval);
    }, []);

    const testimonials = [
        {
            name: 'Sophie Martin',
            role: 'CEO, DesignCo',
            text: 'FacturePro a transformé notre processus de facturation. On gagne 10h par semaine !',
            rating: 5,
            avatar: 'https://randomuser.me/api/portraits/women/1.jpg',
        },
        {
            name: 'Marc Dubois',
            role: 'Freelance Developer',
            text: 'Interface intuitive et fonctionnalités complètes. Exactement ce que je cherchais.',
            rating: 4,
            avatar: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8hUz-P7Cz2ZKn2DEN_EvFhjwmRiURX7r9TA&s',
        },
        {
            name: 'Julie Bernard',
            role: 'Directrice Finance, TechStart',
            text: 'Le meilleur outil de facturation que nous ayons utilisé. Support réactif !',
            rating: 5,
            avatar: 'https://img.freepik.com/vecteurs-libre/illustration-du-jeune-homme-souriant_1308-174669.jpg?semt=ais_hybrid&w=740&q=80',
        },
    ];

    const stats = [
        { value: '10K+', label: 'Entreprises' },
        { value: '500K+', label: 'Factures créées' },
        { value: '99.9%', label: 'Uptime' },
        { value: '24/7', label: 'Support' },
    ];

    return (
        <div className="min-h-screen bg-gradient-to-br from-white to-gray-100 transition-colors duration-300 dark:from-gray-950 dark:to-black">
            <Head title="Accueil" />
            {/* Header */}
            <header
                className={`fixed top-0 z-50 w-full transition-all duration-500 ${
                    isScrolled
                        ? 'border-b border-gray-200 bg-white/90 shadow-sm backdrop-blur-lg dark:border-gray-700/50 dark:bg-slate-900/90'
                        : 'border-b border-transparent bg-transparent'
                }`}
            >
                <nav className="container mx-auto px-6 py-4">
                    <div className="flex items-center justify-between">
                        {/* Logo */}
                        <a href="/" className="group flex items-center space-x-2">
                            {auth?.user?.company_id && company?.company_logo ? (
                                <img
                                    src={`http://localhost:8000/storage/${company.company_logo}`}
                                    alt={company.company_name || 'Logo'}
                                    className="h-8 w-auto rounded object-cover"
                                />
                            ) : (
                                <img src={`/facture-pro.png`} alt={'Logo'} className="h-8 w-auto rounded object-cover" />
                            )}
                            <span
                                className={`text-2xl font-bold transition-colors duration-300 ${
                                    isScrolled
                                        ? 'bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent dark:from-blue-400 dark:to-blue-300'
                                        : 'text-white drop-shadow-lg'
                                }`}
                            >
                                {auth?.user?.company_id ? company?.company_name : 'FacturePro'}
                            </span>
                        </a>

                        {/* Desktop Navigation */}
                        <div className="hidden items-center space-x-8 md:flex">
                            {navLinks.map((link) => (
                                <a
                                    key={link.href}
                                    href={link.href}
                                    className={`font-medium transition duration-300 ${
                                        isScrolled
                                            ? 'text-gray-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400'
                                            : 'text-white drop-shadow-md hover:text-blue-200'
                                    }`}
                                >
                                    {link.label}
                                </a>
                            ))}
                        </div>

                        {/* Desktop Auth Section */}
                        <div className="hidden items-center space-x-4 md:flex">
                            {auth.user ? (
                                <a
                                    href="/dashboard"
                                    className={`flex items-center space-x-3 rounded-lg border px-4 py-2 backdrop-blur-sm transition-all duration-300 hover:shadow-md ${
                                        isScrolled
                                            ? 'border-gray-300 bg-white dark:border-slate-600 dark:bg-slate-800/50'
                                            : 'border-white/30 bg-white/20 hover:bg-white/30'
                                    }`}
                                >
                                    <img
                                        src={
                                            auth.user.avatar ||
                                            `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=3b82f6&color=fff`
                                        }
                                        alt={auth.user.name}
                                        className="h-8 w-8 rounded-full border-2 border-blue-600 dark:border-blue-400"
                                    />
                                    <div className="flex flex-col">
                                        <span className={`text-sm font-semibold ${isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'}`}>
                                            {auth.user.name}
                                        </span>
                                        <span className={`text-xs ${isScrolled ? 'text-gray-600 dark:text-slate-400' : 'text-white/80'}`}>
                                            Voir le tableau de bord
                                        </span>
                                    </div>
                                </a>
                            ) : (
                                <>
                                    <a
                                        href="/login"
                                        className={`flex items-center space-x-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-2.5 font-medium text-white transition duration-300 hover:from-blue-700 hover:to-blue-600 hover:shadow-xl`}
                                    >
                                        <LogIn className="h-4 w-4" />
                                        <span>Connexion</span>
                                    </a>
                                    {/* <a
                                        href="/company?plan=free"
                                        className="flex items-center space-x-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-2.5 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:from-blue-700 hover:to-blue-600 hover:shadow-xl"
                                    >
                                        <UserPlus className="h-4 w-4" />
                                        <span>Commencer</span>
                                    </a> */}
                                </>
                            )}
                        </div>

                        {/* Mobile Menu Button */}
                        <button
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className={`rounded-lg p-2 transition md:hidden ${
                                isScrolled
                                    ? 'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-slate-800'
                                    : 'text-white hover:bg-white/10'
                            }`}
                            aria-label="Toggle menu"
                        >
                            {mobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                        </button>
                    </div>

                    {/* Mobile Menu */}
                    {mobileMenuOpen && (
                        <div
                            className={`mt-4 space-y-4 rounded-lg border-t bg-white/90 px-4 pt-4 pb-4 backdrop-blur-lg md:hidden dark:bg-slate-900/90 ${
                                isScrolled
                                    ? 'rounded-none border-gray-200 bg-transparent px-0 dark:border-slate-700 dark:bg-transparent'
                                    : 'border-white/20'
                            }`}
                        >
                            {navLinks.map((link) => (
                                <a
                                    key={link.href}
                                    href={link.href}
                                    onClick={() => setMobileMenuOpen(false)}
                                    className={`block py-2 font-medium transition ${
                                        isScrolled
                                            ? 'text-gray-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400'
                                            : 'text-white hover:text-blue-200'
                                    }`}
                                >
                                    {link.label}
                                </a>
                            ))}

                            {auth.user ? (
                                <a
                                    href="/dashboard"
                                    className={`mt-4 flex items-center space-x-3 rounded-lg border px-4 py-3 ${
                                        isScrolled
                                            ? 'border-gray-300 bg-white dark:border-slate-600 dark:bg-slate-800/50'
                                            : 'border-white/30 bg-white/10'
                                    }`}
                                >
                                    <img
                                        src={
                                            auth.user.avatar ||
                                            `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=3b82f6&color=fff`
                                        }
                                        alt={auth.user.name}
                                        className="h-10 w-10 rounded-full border-2 border-blue-600"
                                    />
                                    <div className="flex flex-col">
                                        <span className={`text-sm font-semibold ${isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'}`}>
                                            {auth.user.name}
                                        </span>
                                        <span className={`text-xs ${isScrolled ? 'text-gray-600 dark:text-slate-400' : 'text-white/80'}`}>
                                            {auth.user.email}
                                        </span>
                                    </div>
                                </a>
                            ) : (
                                <div className="flex flex-col space-y-3 pt-2">
                                    <a
                                        href="/login"
                                        className={`flex items-center justify-center space-x-2 rounded-lg border px-6 py-3 text-center font-medium transition ${
                                            isScrolled
                                                ? 'border-gray-300 text-gray-900 hover:bg-gray-50 dark:border-slate-600 dark:text-white dark:hover:bg-slate-800'
                                                : 'border-white/30 bg-white/10 text-white hover:bg-white/20'
                                        }`}
                                    >
                                        <LogIn className="h-4 w-4" />
                                        <span>Connexion</span>
                                    </a>
                                    {/* <a
                                        href="/company?plan=free"
                                        className="flex items-center justify-center space-x-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3 text-center font-semibold text-white transition hover:from-blue-700 hover:to-blue-600"
                                    >
                                        <UserPlus className="h-4 w-4" />
                                        <span>Commencer gratuitement</span>
                                    </a> */}
                                </div>
                            )}
                        </div>
                    )}
                </nav>
            </header>

            {/* Hero Section */}
            <section className="relative overflow-hidden px-6 pt-32 pb-20">
                {/* Background Image with Overlay */}
                <div className="absolute inset-0 z-0">
                    <img
                        src="https://www.arapl.org/wp-content/uploads/2024/10/se-preparer-a-la-facturartion-electronique.jpg"
                        alt="Background"
                        className="h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-br from-gray-700/50 via-black/50 to-gray-700/50 dark:from-gray-700/10 dark:via-black/50 dark:to-gray-700/50"></div>
                </div>

                <div className="relative z-10 container mx-auto mt-4 text-center">
                    <div className="mb-4 inline-block rounded-full border border-blue-500 bg-blue-500/50 px-4 py-2">
                        <span className="text-sm font-semibold text-indigo-100">✨ Bienvenue sur FacturePro</span>
                    </div>

                    <h1 className="mb-6 text-5xl leading-tight font-bold text-white md:text-7xl">
                        Facturez en toute
                        <span className="bg-gradient-to-r from-blue-600 to-cyan-500 bg-clip-text text-transparent dark:from-blue-400 dark:to-cyan-400">
                            {' '}
                            simplicité
                        </span>
                    </h1>

                    <p className="mx-auto mb-12 max-w-3xl text-xl text-gray-100 dark:text-slate-300">
                        Centralisez la gestion de vos clients, créez et envoyez vos devis et factures en quelques clics, suivez les paiements,
                        automatisez les relances et pilotez votre activité depuis une seule plateforme simple, sécurisée et performante.
                    </p>

                    <div className="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a
                            href="/company?plan=free"
                            className="group transform rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-8 py-2 text-lg font-bold text-white shadow-2xl shadow-blue-500/40 transition hover:scale-105 hover:from-blue-700 hover:to-blue-600 hover:shadow-blue-500/60"
                        >
                            Commencer gratuitement
                            <span className="ml-2 inline-block transition-transform group-hover:translate-x-1">→</span>
                        </a>
                        <a
                            href="https://t.me/facturtion_bot"
                            target="_blank"
                            className="flex items-center gap-3 rounded-xl border border-gray-300 bg-white px-8 py-2 text-lg font-bold text-blue-500 transition hover:border-blue-600 hover:bg-blue-500 hover:text-white"
                        >
                            Avec telegram <FaTelegram className="h-6 w-6" />
                        </a>
                    </div>

                    <p className="mt-6 text-sm text-gray-200 dark:text-slate-200">✓ Aucune carte bancaire requise • ✓ 14 jours d'essai gratuit</p>
                </div>
            </section>

            {/* Features Section */}
            <section id="features" className="bg-transparent px-4 py-16 transition-colors">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-12 text-center">
                        <h2 className="mb-4 text-4xl font-bold text-gray-900 dark:text-white">Tout ce dont vous avez besoin</h2>
                        <p className="text-xl text-gray-600 dark:text-slate-400">Une solution complète pour gérer votre facturation</p>
                    </div>

                    <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {features.map((feature, index) => (
                            <FeatureCard key={index} {...feature} />
                        ))}
                    </div>
                </div>
            </section>

            {/* Pricing Section */}
            <section id="pricing" className="relative overflow-hidden px-6 py-20">
                <div className="absolute inset-0 z-0">
                    <img
                        src="https://cdn-s-www.ledauphine.com/images/A6FF3DE7-C510-43E2-A2B5-6E1FDD47CC7F/NW_raw/photo-adobe-stock-1760429773.jpg"
                        alt="Background"
                        className="h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-br from-gray-700/50 via-black/50 to-gray-700/50 dark:from-gray-700/10 dark:via-black/50 dark:to-gray-700/50"></div>
                </div>

                <div className="relative z-10 container mx-auto">
                    <div className="mb-16 text-center">
                        <h2 className="mb-4 text-4xl font-bold text-white md:text-5xl">Des tarifs adaptés à votre croissance</h2>
                        <p className="text-lg text-gray-200">Commencez gratuitement, évoluez quand vous êtes prêt</p>
                    </div>

                    <div className="mx-auto grid max-w-full gap-8 md:grid-cols-3">
                        {plans.map((plan, index) => (
                            <PricingCard key={index} {...plan} />
                        ))}
                    </div>
                </div>
            </section>

            {/* FAQ Section */}
            <section id="faq" className="px-6 py-14">
                <div className="container mx-auto max-w-7xl">
                    <div className="mb-20 text-center">
                        <div className="mb-6 inline-block rounded-full border px-4 py-2 dark:border-blue-500/30 dark:bg-blue-500/10">
                            <span className="text-sm font-semibold text-blue-400">FAQ</span>
                        </div>
                        <h2 className="mb-6 text-4xl font-bold md:text-6xl dark:text-white">Questions fréquentes</h2>
                        <p className="text-xl dark:text-slate-400">Tout ce que vous devez savoir sur FacturePro</p>
                    </div>

                    <div className="grid grid-cols-1 space-y-6 space-x-4 md:grid-cols-2">
                        {questions.map((faq, i) => (
                            <div
                                key={i}
                                className="group rounded-2xl border p-6 backdrop-blur-sm transition hover:border-blue-500/50 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <h3 className="mb-3 text-xl font-bold transition group-hover:text-blue-400 dark:text-white">{faq.question}</h3>
                                <p className="leading-relaxed text-gray-700 dark:text-slate-400">{faq.answer}</p>
                            </div>
                        ))}
                    </div>

                    <div className="mt-16 rounded-2xl border border-blue-500/30 bg-gradient-to-br from-blue-600/10 to-cyan-600/10 p-8 text-center backdrop-blur-sm">
                        <h3 className="mb-3 text-2xl font-bold dark:text-white">Vous avez d'autres questions ?</h3>
                        <p className="mb-6 text-gray-600 dark:text-slate-300">
                            Notre équipe est là pour vous aider. Contactez-nous et nous vous répondrons rapidement.
                        </p>
                        <a
                            href="/contact"
                            className="inline-flex items-center rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700"
                        >
                            Contacter le support
                            <ArrowRight className="ml-2 h-5 w-5" />
                        </a>
                    </div>
                </div>
            </section>

            {/* Testimonials */}
            <section
                id="testimonials"
                className="relative bg-gradient-to-r from-blue-200 to-purple-400 p-16 px-6 dark:from-purple-700/90 dark:to-blue-600/90"
            >
                <div className="relative z-10 mx-auto">
                    <div className="mb-10 text-center">
                        <h2 className="mb-6 text-4xl font-bold text-white md:text-6xl">Ils nous font confiance</h2>
                        <p className="text-xl text-slate-200">Rejoignez des milliers d'entreprises satisfaites</p>
                    </div>

                    <div className="mx-auto max-w-4xl">
                        <div className="relative min-h-[200px]">
                            {testimonials.map((testimonial, i) => (
                                <div
                                    key={i}
                                    className={`transition-all duration-500 ${
                                        i === activeTestimonial ? 'relative opacity-100' : 'pointer-events-none absolute inset-0 opacity-0'
                                    }`}
                                >
                                    <div className="group relative overflow-hidden rounded-3xl border-4 border-white bg-gradient-to-br from-pink-600 via-purple-600 to-pink-700 p-10 shadow-2xl backdrop-blur-md transition-all duration-300 dark:border-slate-700 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800">
                                        <div className="flex flex-col items-center gap-8 md:flex-row md:items-start md:gap-12">
                                            {/* Left side - Avatar with decorative elements */}
                                            <div className="relative flex-shrink-0">
                                                {/* Decorative background shape */}
                                                <div className="absolute -top-8 -left-8 h-48 w-48 rounded-full bg-gradient-to-br from-orange-400/30 to-yellow-400/30 blur-2xl"></div>
                                                <div className="absolute -bottom-4 left-0 h-64 w-24 rounded-full bg-gradient-to-b from-orange-400/40 to-pink-400/40"></div>

                                                {/* Avatar container with circle background */}
                                                <div className="relative z-10">
                                                    <div className="absolute inset-0 -m-4 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 opacity-50"></div>
                                                    <img
                                                        src={
                                                            testimonial.avatar ||
                                                            `https://ui-avatars.com/api/?name=${encodeURIComponent(testimonial.name)}&size=200&background=random&bold=true`
                                                        }
                                                        alt={testimonial.name}
                                                        className="relative h-48 w-48 rounded-full border-8 border-white object-cover shadow-2xl dark:border-slate-700"
                                                    />
                                                </div>
                                            </div>

                                            {/* Right side - Content */}
                                            <div className="flex-1 text-left">
                                                {/* Quote icon top */}
                                                <div className="mb-4 flex justify-start">
                                                    <svg className="h-12 w-12 text-cyan-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                                    </svg>
                                                </div>

                                                {/* Author info */}
                                                <div className="mb-4">
                                                    <div className="mb-1 text-2xl font-bold text-white">
                                                        {testimonial.name}{' '}
                                                        <span className="text-lg font-normal text-pink-200">- {testimonial.role}</span>
                                                    </div>
                                                </div>

                                                {/* Rating stars */}
                                                <div className="mb-6 flex gap-1">
                                                    {[...Array(testimonial.rating)].map((_, j) => (
                                                        <Star key={j} className="h-6 w-6 fill-yellow-400 text-yellow-400" />
                                                    ))}
                                                </div>

                                                {/* Title */}
                                                <h3 className="mb-4 text-2xl font-bold tracking-wide text-white uppercase md:text-3xl">
                                                    Amazing Customer Service
                                                </h3>

                                                {/* Testimonial text */}
                                                <p className="mb-6 text-lg leading-relaxed text-white/95 md:text-xl">{testimonial.text}</p>

                                                {/* Quote icon bottom */}
                                                <div className="flex justify-end">
                                                    <svg className="h-12 w-12 rotate-180 text-cyan-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="mt-6 flex justify-center gap-2">
                            {testimonials.map((_, i) => (
                                <button
                                    key={i}
                                    onClick={() => setActiveTestimonial(i)}
                                    aria-label={`Voir le témoignage ${i + 1}`}
                                    className={`h-3 rounded-full transition-all duration-300 ${
                                        i === activeTestimonial
                                            ? 'w-8 bg-blue-500'
                                            : 'w-3 bg-gray-300 hover:bg-gray-400 dark:bg-slate-700 dark:hover:bg-slate-600'
                                    }`}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="bg-gradient-to-r px-6 py-20">
                <div className="container mx-auto text-center">
                    <h2 className="mb-6 text-4xl font-bold md:text-5xl dark:text-white">Prêt à simplifier votre facturation ?</h2>
                    <p className="mx-auto mb-8 max-w-2xl text-xl text-blue-500 dark:text-blue-100">
                        Rejoignez plus de 10,000 entreprises qui font confiance à FacturePro
                    </p>
                    <a
                        href="/company?plan=free"
                        className="inline-block transform rounded-xl bg-white px-10 py-4 text-lg font-bold text-blue-600 shadow-xl transition hover:scale-105 hover:bg-blue-600 hover:text-white"
                    >
                        Commencer maintenant
                    </a>
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t border-gray-200 bg-gradient-to-r from-blue-600 to-blue-500 py-12 transition-colors duration-300 dark:border-slate-800 dark:from-blue-600/90 dark:to-purple-700/90">
                <div className="container mx-auto">
                    <div className="mb-8 grid gap-8 md:grid-cols-4">
                        <div>
                            <div className="mb-4 flex items-center space-x-2">
                                <img src={`/facture-pro.png`} alt={'Logo'} className="h-8 w-auto rounded object-cover" />
                                <span className="text-xl font-bold text-white">FacturePro</span>
                            </div>
                            <p className="text-sm text-slate-200">La solution moderne pour gérer vos factures professionnelles.</p>
                        </div>

                        <div>
                            <h4 className="mb-4 font-semibold text-white">Produit</h4>
                            <ul className="space-y-2 text-sm text-slate-200">
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Fonctionnalités
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Tarifs
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Documentation
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="mb-4 font-semibold text-white">Entreprise</h4>
                            <ul className="space-y-2 text-sm text-slate-200">
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        À propos
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Blog
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Contact
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="mb-4 font-semibold text-white">Légal</h4>
                            <ul className="space-y-2 text-sm text-slate-200">
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Confidentialité
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        CGU
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="transition hover:text-white">
                                        Mentions légales
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="border-t border-gray-400 pt-8 text-center text-sm text-gray-200">© 2024 FacturePro. Tous droits réservés.</div>
                </div>
            </footer>

            {/* Floating Scroll to Top Button */}
            {showScrollTop && (
                <button
                    onClick={scrollToTop}
                    className="fixed right-8 bottom-8 z-50 flex h-12 w-12 cursor-pointer items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-md shadow-blue-500/30 transition-all hover:scale-110 hover:shadow-lg hover:shadow-blue-500/50"
                    aria-label="Retour en haut"
                >
                    <ArrowUp className="h-6 w-6" />
                </button>
            )}
        </div>
    );
}
