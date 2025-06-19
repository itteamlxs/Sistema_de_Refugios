<section id="faqs" class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-blue-600 mb-8">Preguntas Frecuentes</h2>
        <div x-data="faqsData()" class="space-y-2">
            <template x-for="(faq, index) in faqs" :key="index">
                <div class="border border-gray-200 rounded-md">
                    <button
                        @click="openIndex = openIndex === index ? null : index"
                        class="w-full flex justify-between items-center p-4 text-left font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :aria-expanded="openIndex === index"
                        :aria-controls="'faq-answer-' + index"
                    >
                        <span x-text="faq.question"></span>
                        <svg
                            :class="{ 'rotate-180': openIndex === index }"
                            class="w-5 h-5 transform transition-transform duration-200"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div
                        x-show="openIndex === index"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-screen"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-screen"
                        x-transition:leave-end="opacity-0 max-h-0"
                        :id="'faq-answer-' + index"
                        class="p-4 pt-0 text-gray-600 overflow-hidden"
                    >
                        <p x-text="faq.answer"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('faqsData', () => ({
                openIndex: null,
                faqs: [
                    {
                        question: '¿Cómo funciona el nuevo sistema de filtro?',
                        answer: 'El sistema utiliza un filtro progresivo inteligente. Solo escribe las primeras letras del nombre y verás únicamente las personas cuyos nombres empiecen con esas letras. Por ejemplo, si escribes "Mar" verás "María", "Marco", "Margarita", etc. Los resultados se actualizan automáticamente mientras escribes.'
                    },
                    {
                        question: '¿Qué diferencia hay entre filtrar y buscar?',
                        answer: 'El filtro muestra solo nombres que EMPIECEN con las letras que escribas, mientras que una búsqueda tradicional mostraría cualquier nombre que CONTENGA esas letras en cualquier parte. Nuestro sistema es más preciso y rápido para encontrar personas específicas.'
                    },
                    {
                        question: '¿Qué información aparece en los resultados?',
                        answer: 'Los resultados muestran el nombre completo, estatus actual (Albergado, Pendiente, En tránsito, Dado de alta), fecha y hora de ingreso, nombre del refugio donde se encuentra, y la ubicación del refugio.'
                    },
                    {
                        question: '¿Cómo descargo la lista completa de un refugio?',
                        answer: 'En la sección "Explorar Refugios", encontrarás cada refugio listado con botones "CSV" y "PDF". Haz clic en el formato que prefieras para descargar la lista completa de personas albergadas en ese refugio específico.'
                    },
                    {
                        question: '¿Puedo ver todas las personas sin filtrar?',
                        answer: 'Sí, cuando cargas la página verás automáticamente las personas albergadas más recientes. Si has aplicado un filtro, puedes hacer clic en la "X" del campo de búsqueda para limpiar el filtro y ver la lista completa nuevamente.'
                    },
                    {
                        question: '¿Qué significan los diferentes colores de estatus?',
                        answer: 'Los estatus tienen colores distintivos: Verde para "Albergado" (persona actualmente en el refugio), Amarillo para "Pendiente" (en proceso), Azul para "En tránsito" (moviéndose entre refugios), y Gris para "Dado de alta" (ya no está en el refugio).'
                    },
                    {
                        question: '¿Cómo registro un nuevo refugio?',
                        answer: 'Los administradores pueden registrar refugios iniciando sesión y subiendo un archivo CSV con los datos en la sección de administración. Los refugios deben ser aprobados antes de aparecer públicamente en el sistema.'
                    },
                    {
                        question: '¿Es segura mi información en el sistema?',
                        answer: 'Sí, utilizamos medidas de seguridad avanzadas, como validación CSRF, sanitización de datos y control de acceso por roles para proteger toda la información del sistema.'
                    },
                    {
                        question: '¿Qué hago si no encuentro a la persona que busco?',
                        answer: 'Verifica que estés escribiendo las primeras letras del nombre correctamente. Recuerda que el sistema filtra por el INICIO del nombre. Si aún no aparece, es posible que la persona no esté registrada en un refugio aprobado o que esté registrada con un nombre diferente.'
                    }
                ]
            }));
        });
    </script>
</section>