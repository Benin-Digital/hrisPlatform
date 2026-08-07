<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import ModalCandidature from '@/Components/ModalCandidature.vue';
import {
    RocketLaunchIcon,
    ChartBarIcon,
    BriefcaseIcon,
    MapPinIcon,
    PencilSquareIcon,
    AcademicCapIcon,
    CameraIcon,
    PencilIcon,
    BellIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    BookOpenIcon,
    NewspaperIcon,
    EnvelopeIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    annonces: Array,
    gallery: Array,
    statistiquesPubliques: Array,
    offres: Array,
    stages: Array,
    formations: Array,
});

const isScrolled = ref(false);
const showCandidatureModal = ref(false);
const selectedOffre = ref(null);

const openCandidature = (offre) => {
    selectedOffre.value = offre;
    showCandidatureModal.value = true;
};
const formLoading = ref(false);
const message = ref({ type: '', text: '' });
const currentSlide = ref(0);

const nextSlide = () => {
    if (props.gallery && props.gallery.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % props.gallery.length;
    }
};

const prevSlide = () => {
    if (props.gallery && props.gallery.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + props.gallery.length) % props.gallery.length;
    }
};

onMounted(() => {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 50;
    });

    setInterval(() => {
        nextSlide();
    }, 5000);
});

const newsletterEmail = ref('');
const subscribeNewsletter = async () => {
    if (newsletterEmail.value) {
        formLoading.value = true;
        try {
            const response = await axios.post(route('newsletter.subscribe'), {
                email: newsletterEmail.value
            });
            message.value = { type: 'success', text: 'Inscription réussie !' };
            newsletterEmail.value = '';
        } catch (error) {
            message.value = { 
                type: 'error', 
                text: error.response?.data?.message || 'Une erreur est survenue.' 
            };
        } finally {
            formLoading.value = false;
            setTimeout(() => { message.value = { type: '', text: '' }; }, 5000);
        }
    }
};

const shareOnSocial = (platform) => {
    alert(`Partage sur ${platform} (Simulation)`);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Date inconnue';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const cleanContent = (html) => {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || "";
};
</script>

<template>
    <Head title="Bienvenue sur HRIS Pro" />

    <div class="min-h-screen bg-gradient-to-br from-white via-emerald-50/30 to-white text-gray-900 font-sans selection:bg-emerald-600 selection:text-white">
        <div class="fixed inset-0 overflow-hidden -z-10">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-emerald-500/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[10%] -right-[5%] w-[40%] h-[40%] bg-green-500/5 rounded-full blur-[100px]"></div>
        </div>

        <!-- Navbar -->
        <nav :class="[
            'fixed top-0 w-full z-50 transition-all duration-500 px-6 py-4 lg:px-12',
            isScrolled ? 'bg-white/80 backdrop-blur-xl border-b border-gray-200 py-3 shadow-sm' : 'bg-transparent'
        ]">
            <div class="max-w-7xl mx-auto flex justify-between items-center text-gray-900">
                <div class="flex items-center gap-3 group cursor-pointer">
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-all">
                        <span class="text-white font-black text-xl">H</span>
                    </div>
                    <span class="text-2xl font-black tracking-tight">HRIS<span class="text-emerald-600">Pro</span></span>
                </div>

                <div class="flex items-center gap-4">
                    <nav v-if="canLogin" class="flex items-center gap-3">
                        <!-- Offres -->
                        <Link
                            href="/offres"
                            class="px-4 py-2 text-gray-600 hover:text-emerald-600 font-bold text-sm transition"
                        >
                            Offres
                        </Link>

                        <!--  Stages (redirige vers le formulaire) -->
                        <Link
                            href="/stage/demande"
                            class="px-4 py-2 text-gray-600 hover:text-emerald-600 font-bold text-sm transition"
                        >
                            Stages
                        </Link>

                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-black hover:bg-emerald-700 transition shadow-lg"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-black hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/20 uppercase text-xs tracking-widest"
                            >
                                Connexion
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="pt-40 pb-20 px-6">
            <div class="max-w-7xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-black uppercase tracking-[0.2em] mb-8 animate-fade-in">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-600 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                    </span>
                    Plateforme Collaborative
                </div>

                <h1 class="text-5xl lg:text-8xl font-black leading-[1.1] text-gray-900 mb-8">
                    Gérez votre personnel, <br/>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600">boostez votre futur.</span>
                </h1>
                
                <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-12 leading-relaxed font-medium">
                    Une expérience utilisateur immersive conçue pour transformer la collaboration et la gestion de vos ressources humaines.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-6 mb-24">
                    <Link :href="route('login')" class="px-10 py-5 bg-emerald-600 text-white rounded-2xl font-black text-lg hover:bg-emerald-700 hover:scale-105 active:scale-95 transition-all shadow-lg shadow-emerald-500/30 uppercase tracking-widest">
                        Commencer maintenant
                    </Link>
                    <a href="#features" class="px-10 py-5 bg-white text-gray-900 border border-gray-300 rounded-2xl font-black text-lg hover:bg-gray-50 active:scale-95 transition-all uppercase tracking-widest">
                        Explorer les outils
                    </a>
                </div>
            </div>
        </main>

        <!-- Statistics Section -->
        <section v-if="statistiquesPubliques && statistiquesPubliques.length > 0" class="py-20 bg-emerald-50 border-y border-emerald-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-1/3 h-full bg-emerald-500/5 blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-1/3 h-full bg-green-500/5 blur-[100px]"></div>

            <div class="max-w-7xl mx-auto px-6 relative">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-5xl font-black text-gray-900 mb-4 uppercase tracking-tighter italic flex items-center justify-center gap-3">
                        Impact & Performance
                        <RocketLaunchIcon class="w-10 h-10 text-emerald-600" />
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto font-medium">Des résultats concrets qui témoignent de notre dynamique.</p>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(kpi, index) in statistiquesPubliques[0].data.kpis" :key="index" 
                         class="group relative bg-white p-8 rounded-3xl border border-emerald-100 hover:border-emerald-300 transition-all duration-500 hover:-translate-y-2 shadow-sm hover:shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <div class="relative z-10">
                            <div class="text-4xl mb-4 grayscale group-hover:grayscale-0 transition duration-500">{{ kpi.icon || '📈' }}</div>
                            <div class="text-4xl lg:text-5xl font-black text-gray-900 mb-2 tracking-tight group-hover:text-emerald-600 transition-colors">
                                {{ kpi.value }}
                            </div>
                            <div class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ kpi.label }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 text-center text-gray-500 text-xs font-bold uppercase tracking-widest">
                    Mise à jour : {{ new Date(statistiquesPubliques[0].created_at).toLocaleDateString('fr-FR') }}
                </div>
            </div>
        </section>

        <!-- Job Offers Section -->
        <section v-if="offres && offres.length > 0" class="py-32 bg-white relative border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
                    <div>
                        <h2 class="text-4xl lg:text-6xl font-black text-gray-900 mb-6 uppercase tracking-tighter italic flex items-center gap-4">
                            Carrières & Opportunités
                            <BriefcaseIcon class="w-12 h-12 text-emerald-600" />
                        </h2>
                        <div class="h-1 w-20 bg-emerald-500 rounded-full mb-6"></div>
                        <p class="text-xl text-gray-600 font-bold max-w-2xl">Rejoignez une équipe passionnée et construisez le futur avec nous.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="offre in offres" :key="offre.id" class="group relative bg-gray-50 border border-gray-200 rounded-3xl p-8 hover:bg-emerald-50 hover:border-emerald-300 transition-all duration-300 shadow-sm hover:shadow-md">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block">{{ offre.type_contrat }}</span>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-600 transition-colors">{{ offre.titre }}</h3>
                            </div>
                            <span class="text-gray-500 text-sm font-bold flex items-center gap-2">
                                <MapPinIcon class="w-5 h-5 text-gray-500" />
                                {{ offre.lieu || 'Non spécifié' }}
                            </span>
                        </div>
                        <p class="text-gray-600 mb-8 line-clamp-3 leading-relaxed">{{ offre.description }}</p>
                        
                        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                            <span class="text-gray-500 text-xs uppercase tracking-widest font-bold">Publié le {{ new Date(offre.created_at).toLocaleDateString() }}</span>
                            <button @click="openCandidature(offre)" class="text-white bg-emerald-600 hover:bg-emerald-700 px-6 py-2 rounded-xl font-bold text-sm transition shadow-md shadow-emerald-500/20">
                                Postuler
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions : Voir toutes les offres & Candidature spontanée -->
                <div class="text-center mt-12 flex flex-wrap justify-center gap-4">
                    <Link
                        href="/offres"
                        class="inline-block px-8 py-4 bg-emerald-600 text-white rounded-2xl font-black hover:bg-emerald-700 transition shadow-lg hover:scale-105 active:scale-95 flex items-center gap-2"
                    >
                        Voir toutes les offres
                        <ArrowRightIcon class="w-5 h-5" />
                    </Link>
                    <Link
                        href="/candidature/spontanee"
                        class="inline-block px-8 py-4 bg-white text-emerald-600 border-2 border-emerald-600 rounded-2xl font-black hover:bg-emerald-50 transition shadow-lg hover:scale-105 active:scale-95 flex items-center gap-2"
                    >
                        <PencilSquareIcon class="w-5 h-5" />
                        Candidature spontanée
                    </Link>
                </div>
            </div>
            
            <ModalCandidature 
                :show="showCandidatureModal" 
                :offre="selectedOffre" 
                @close="showCandidatureModal = false" 
            />
        </section>

        <!-- 🎓 Stages Section -->
        <section v-if="stages && stages.length > 0" class="py-32 bg-emerald-50/30 border-y border-emerald-100 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
                    <div>
                        <h2 class="text-4xl lg:text-6xl font-black text-gray-900 mb-6 uppercase tracking-tighter italic flex items-center gap-4">
                            Stages à pourvoir
                            <AcademicCapIcon class="w-12 h-12 text-emerald-600" />
                        </h2>
                        <div class="h-1 w-20 bg-emerald-500 rounded-full mb-6"></div>
                        <p class="text-xl text-gray-600 font-bold max-w-2xl">Démarrez votre carrière avec nous et développez vos compétences.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="stage in stages" :key="stage.id" class="group relative bg-white border border-gray-200 rounded-3xl p-8 hover:bg-emerald-50 hover:border-emerald-300 transition-all duration-300 shadow-sm hover:shadow-md">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block">{{ stage.type_contrat }}</span>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-600 transition-colors">{{ stage.titre }}</h3>
                            </div>
                            <span class="text-gray-500 text-sm font-bold flex items-center gap-2">
                                <MapPinIcon class="w-5 h-5 text-gray-500" />
                                {{ stage.lieu || 'Non spécifié' }}
                            </span>
                        </div>
                        <p class="text-gray-600 mb-8 line-clamp-3 leading-relaxed">{{ stage.description }}</p>
                        
                        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                            <span class="text-gray-500 text-xs uppercase tracking-widest font-bold">Publié le {{ new Date(stage.created_at).toLocaleDateString() }}</span>
                            <button @click="openCandidature(stage)" class="text-white bg-emerald-600 hover:bg-emerald-700 px-6 py-2 rounded-xl font-bold text-sm transition shadow-md shadow-emerald-500/20">
                                Postuler
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <Link
                        href="/offres?type=Stage"
                        class="inline-block px-8 py-4 bg-emerald-600 text-white rounded-2xl font-black hover:bg-emerald-700 transition shadow-lg hover:scale-105 active:scale-95 flex items-center gap-2"
                    >
                        Voir tous les stages
                        <ArrowRightIcon class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Trainings Preview Section -->
        <section v-if="formations && formations.length > 0" class="py-32 bg-gradient-to-b from-white to-emerald-50/50 relative">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-20">
                    <h2 class="text-4xl lg:text-6xl font-black text-gray-900 mb-6 uppercase tracking-tighter italic flex items-center justify-center gap-4">
                        Formations à la Une
                        <AcademicCapIcon class="w-12 h-12 text-emerald-600" />
                    </h2>
                    <div class="h-1 w-20 bg-emerald-500 mx-auto rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="formation in formations" :key="formation.id" class="group bg-white border border-gray-200 rounded-[2rem] overflow-hidden hover:border-emerald-300 transition-all duration-500 hover:-translate-y-2 shadow-sm hover:shadow-lg">
                        <div class="aspect-video bg-gray-100 relative overflow-hidden">
                            <img v-if="formation.image_couverture" :src="`/storage/${formation.image_couverture}`" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
                            <div v-else class="w-full h-full bg-gradient-to-br from-emerald-100 to-green-100 flex items-center justify-center">
                                <BookOpenIcon class="w-12 h-12 text-emerald-600" />
                            </div>
                            <div class="absolute top-4 right-4 bg-emerald-600 text-white text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full">
                                {{ formation.duree_minutes }} min
                            </div>
                        </div>

                        <div class="p-8">
                            <h3 class="text-xl font-black text-gray-900 mb-3 group-hover:text-emerald-600 transition-colors line-clamp-1">{{ formation.titre }}</h3>
                            <div class="flex items-center gap-2 mb-6">
                                <span class="text-gray-600 text-sm font-medium">{{ formation.categorie?.nom || 'Formation' }}</span>
                            </div>
                            
                            <Link :href="route('login')" class="w-full block text-center py-3 rounded-xl border border-gray-300 text-gray-900 font-bold hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition uppercase tracking-widest text-xs">
                                Se connecter pour voir
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section v-if="gallery && gallery.length > 0" class="py-32 bg-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-20">
                    <h2 class="text-4xl lg:text-6xl font-black text-gray-900 mb-6 uppercase tracking-tighter italic flex items-center justify-center gap-4">
                        Galerie Moments
                        <CameraIcon class="w-12 h-12 text-emerald-600" />
                    </h2>
                    <div class="h-1 w-20 bg-emerald-500 mx-auto rounded-full"></div>
                </div>

                <div class="relative group">
                    <div class="overflow-hidden rounded-[3rem] border border-gray-200 bg-gray-50 shadow-lg aspect-[21/9]">
                        <div 
                            class="flex transition-transform duration-700 ease-in-out h-full"
                            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
                        >
                            <div 
                                v-for="item in gallery" 
                                :key="item.id" 
                                class="min-w-full h-full relative"
                            >
                                <img 
                                    :src="`/storage/${item.image_path}`" 
                                    :alt="item.title" 
                                    class="w-full h-full object-cover"
                                />
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-12">
                                    <h4 v-if="item.title" class="text-3xl font-black text-white mb-2">{{ item.title }}</h4>
                                    <p v-if="item.description" class="text-gray-200 text-lg max-w-2xl">{{ item.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button 
                        @click="prevSlide"
                        class="absolute left-6 top-1/2 -translate-y-1/2 w-14 h-14 rounded-2xl bg-white/90 backdrop-blur-xl border border-gray-200 text-gray-900 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all opacity-0 group-hover:opacity-100 shadow-lg"
                    >
                        <ArrowLeftIcon class="w-6 h-6" />
                    </button>
                    <button 
                        @click="nextSlide"
                        class="absolute right-6 top-1/2 -translate-y-1/2 w-14 h-14 rounded-2xl bg-white/90 backdrop-blur-xl border border-gray-200 text-gray-900 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all opacity-0 group-hover:opacity-100 shadow-lg"
                    >
                        <ArrowRightIcon class="w-6 h-6" />
                    </button>

                    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-3">
                        <button 
                            v-for="(_, index) in gallery" 
                            :key="index"
                            @click="currentSlide = index"
                            :class="[
                                'w-3 h-3 rounded-full transition-all duration-300',
                                currentSlide === index ? 'bg-emerald-600 w-10' : 'bg-white/60 hover:bg-white'
                            ]"
                        ></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blog/Annonces Preview -->
        <section class="py-32 px-6 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
                    <div>
                        <h2 class="text-5xl lg:text-7xl font-black text-gray-900 italic tracking-tighter uppercase mb-4 flex items-center gap-4">
                            Le Blog HRIS
                            <PencilIcon class="w-12 h-12 text-emerald-600" />
                        </h2>
                        <p class="text-xl text-gray-600 font-bold">Analyses, tendances et vie de l'entreprise</p>
                    </div>
                    <Link :href="route('login')" class="font-black text-emerald-600 hover:text-emerald-700 transition flex items-center gap-3 uppercase tracking-widest text-xs py-3 px-6 border border-emerald-200 rounded-full hover:bg-emerald-50">
                        Consulter le blog
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>
                </div>

                <div v-if="annonces && annonces.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div v-for="annonce in annonces" :key="annonce.id" class="group cursor-pointer">
                        <div class="bg-white aspect-video rounded-[2.5rem] mb-8 overflow-hidden border border-gray-200 bg-cover bg-center transition-all duration-500 group-hover:border-emerald-300 shadow-sm group-hover:shadow-md"
                             :style="annonce.image ? `background-image: url('/storage/${annonce.image}')` : ''">
                            <div v-if="!annonce.image" class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 group-hover:scale-110 transition-all duration-1000"></div>
                        </div>
                        <div class="flex items-center gap-3 text-emerald-600 font-black text-[10px] uppercase tracking-[0.2em] mb-4">
                            <span class="bg-emerald-100 px-2 py-1 rounded text-emerald-700">{{ annonce.type_annonce || 'Actualité' }}</span>
                            <span class="flex-1 h-[1px] bg-gray-200"></span>
                            <span class="text-gray-500">{{ formatDate(annonce.created_at) }}</span>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-600 transition-colors mb-4 line-clamp-1">{{ annonce.titre }}</h3>
                        <p class="text-gray-600 font-medium line-clamp-2 leading-relaxed group-hover:text-gray-700">
                            {{ cleanContent(annonce.contenu) }}
                        </p>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white rounded-[4rem] border border-dashed border-gray-200">
                    <NewspaperIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-400 font-black uppercase tracking-[0.3em] text-sm">Les articles arrivent bientôt...</p>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="py-32 px-6 relative overflow-hidden bg-emerald-600">
            <div class="absolute inset-0 bg-emerald-700 -z-10 skew-y-2 origin-top-right scale-x-125"></div>
            <div class="max-w-4xl mx-auto text-center text-white relative">
                <div class="absolute -top-20 -left-20 w-64 h-64 bg-emerald-500/30 rounded-full blur-[100px]"></div>
                <h2 class="text-4xl lg:text-7xl font-black mb-6 italic uppercase tracking-tighter flex items-center justify-center gap-4">
                    Inscription Newsletter
                    <BellIcon class="w-12 h-12 text-white" />
                </h2>
                <p class="text-emerald-100 text-xl font-medium mb-12 max-w-2xl mx-auto leading-relaxed">
                    Accédez aux meilleures pratiques du management moderne et aux news de votre secteur en exclusivité.
                </p>

                <div v-if="message.text" :class="[
                    'mb-8 p-4 rounded-xl text-sm font-bold uppercase tracking-widest',
                    message.type === 'success' ? 'bg-emerald-500/20 text-emerald-100' : 'bg-rose-500/20 text-rose-100'
                ]">
                    {{ message.text }}
                </div>

                <form @submit.prevent="subscribeNewsletter" class="flex flex-col sm:flex-row gap-4 max-w-xl mx-auto relative z-10">
                    <input 
                        v-model="newsletterEmail"
                        type="email" 
                        required
                        :disabled="formLoading"
                        placeholder="votre@email.com" 
                        class="flex-1 px-8 py-5 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white focus:bg-white/30 transition-all disabled:opacity-50"
                    />
                    <button 
                        type="submit" 
                        :disabled="formLoading"
                        class="px-10 py-5 bg-white text-emerald-700 rounded-2xl font-black uppercase hover:bg-emerald-50 transition active:scale-95 shadow-lg tracking-wider disabled:opacity-50 flex items-center gap-2"
                    >
                        <EnvelopeIcon class="w-5 h-5" />
                        {{ formLoading ? 'Patientez...' : 'S\'abonner' }}
                    </button>
                </form>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 py-32 px-6 text-white overflow-hidden relative border-t border-gray-700">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-start mb-20">
                    <div>
                        <div class="flex items-center gap-3 mb-10 scale-125 origin-left">
                            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center">
                                <span class="text-white font-black text-xl">H</span>
                            </div>
                            <span class="text-2xl font-black tracking-tight text-white">HRIS<span class="text-emerald-400">Pro</span></span>
                        </div>
                        <p class="text-gray-400 text-xl max-w-md mb-12 leading-relaxed font-medium">
                            Construire un futur où l'humain et la technologie convergent pour créer l'excellence.
                        </p>
                        
                        <div class="flex gap-4">
                            <button @click="shareOnSocial('LinkedIn')" class="w-14 h-14 rounded-2xl bg-gray-800 flex items-center justify-center hover:bg-emerald-600 transition-all border border-gray-700 group shadow-lg">
                                <svg class="w-6 h-6 fill-white" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </button>
                            <button @click="shareOnSocial('Twitter')" class="w-14 h-14 rounded-2xl bg-gray-800 flex items-center justify-center hover:bg-emerald-600 transition-all border border-gray-700 group shadow-lg">
                                <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-12">
                        <div>
                            <h4 class="font-black text-white mb-8 uppercase tracking-widest text-xs">Produit</h4>
                            <ul class="space-y-4 text-gray-400 font-bold text-sm">
                                <li class="hover:text-emerald-400 transition cursor-pointer">Fonctionnalités</li>
                                <li class="hover:text-emerald-400 transition cursor-pointer">Sécurité</li>
                                <li class="hover:text-emerald-400 transition cursor-pointer">Architecture</li>
                                <li class="hover:text-emerald-400 transition cursor-pointer">Tarification</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-black text-white mb-8 uppercase tracking-widest text-xs">Ressources</h4>
                            <ul class="space-y-4 text-gray-400 font-bold text-sm">
                                <li class="hover:text-emerald-400 transition cursor-pointer">Documentation</li>
                                <li class="hover:text-emerald-400 transition cursor-pointer">API Support</li>
                                <li class="hover:text-emerald-400 transition cursor-pointer">Status Live</li>
                                <li class="hover:text-emerald-400 transition cursor-pointer">Contact Experts</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="pt-10 border-t border-gray-700 flex flex-col md:flex-row justify-between gap-8 text-[10px] text-gray-500 font-black uppercase tracking-[0.4em]">
                    <p>© 2026 HRIS Pro Ecosystem. Built for Excellence.</p>
                    <div class="flex gap-10">
                        <span class="hover:text-gray-300 transition cursor-pointer italic">Legal Notes</span>
                        <span class="hover:text-gray-300 transition cursor-pointer italic">Privacy Policy</span>
                    </div>
                    <p class="text-gray-700">Stack: Laravel {{ laravelVersion }} / PHP {{ phpVersion }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;500;700;900&display=swap');

:deep(body) {
    font-family: 'Outfit', sans-serif;
    background-color: #09090b;
}

h1, h2, h3, h4, .font-black {
    font-family: 'Outfit', sans-serif;
    font-weight: 900;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 1s ease-out forwards;
}
</style>