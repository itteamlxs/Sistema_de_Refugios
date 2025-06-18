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
                        question: '¿Cómo puedo buscar a una persona albergada?',
                        answer: 'En la sección "Buscar Personas Albergadas", ingrese el nombre de la persona y presione "Buscar". También puede explorar refugios específicos haciendo clic en sus nombres.'
                    },
                    {
                        question: '¿Qué información aparece en los resultados de búsqueda?',
                        answer: 'Los resultados muestran el nombre, estatus, fecha y hora de ingreso, nombre del refugio, y ubicación de las personas albergadas.'
                    },
                    {
                        question: '¿Cómo descargo la lista de un refugio?',
                        answer: 'En la sección "Explorar Refugios", haga clic en los botones "CSV" o "PDF" junto al refugio para descargar la lista de personas albergadas en el formato deseado.'
                    },
                    {
                        question: '¿Cómo registro un nuevo refugio?',
                        answer: 'Los administradores pueden registrar refugios iniciando sesión y subiendo un archivo CSV con los datos en la sección de administración. Los refugios deben ser aprobados antes de aparecer públicamente.'
                    },
                    {
                        question: '¿Es segura mi información en el sistema?',
                        answer: 'Sí, utilizamos medidas de seguridad avanzadas, como validación CSRF y sanitización de datos, para proteger la información de los usuarios.'
                    },
                    {
                        question: '¿Qué hago si no encuentro a la persona que busco?',
                        answer: 'Verifique que el nombre esté escrito correctamente o intente con variaciones. Si no aparece, es posible que la persona no esté registrada en un refugio aprobado.'
                    }
                ]
            }));
        });
    </script>
</section>