import Sortable from 'sortablejs';

// Make Sortable available globally for the builder page
window.Sortable = Sortable;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initQuestionnaireBuilder();
});

function initQuestionnaireBuilder() {
    const builderEl = document.getElementById('questionnaire-builder');
    if (!builderEl) return;

    const questionnaireId = builderEl.dataset.questionnaireId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Sortable for blocks container (single page mode)
    const blocksContainer = document.getElementById('blocks-container');
    if (blocksContainer) {
        new Sortable(blocksContainer, {
            animation: 150,
            handle: '.cursor-move',
            ghostClass: 'opacity-50',
            dragClass: 'shadow-lg',
            onEnd: async function(evt) {
                await saveOrder('blocks', blocksContainer, questionnaireId, csrfToken);
            }
        });
    }

    // Sortable for steps container (wizard mode)
    const stepsContainer = document.getElementById('steps-container');
    if (stepsContainer) {
        new Sortable(stepsContainer, {
            animation: 150,
            handle: '.cursor-move',
            ghostClass: 'opacity-50',
            dragClass: 'shadow-lg',
            onEnd: async function(evt) {
                await saveOrder('steps', stepsContainer, questionnaireId, csrfToken);
            }
        });

        // Also make blocks within each step sortable
        stepsContainer.querySelectorAll('.step-blocks-container').forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'opacity-50',
                group: 'blocks',
                onEnd: async function(evt) {
                    await saveOrder('blocks', container, questionnaireId, csrfToken);
                }
            });
        });
    }

    // Sortable for questions within each block
    document.querySelectorAll('.questions-container').forEach(container => {
        new Sortable(container, {
            animation: 150,
            handle: '.cursor-move',
            ghostClass: 'opacity-50',
            group: 'questions',
            onEnd: async function(evt) {
                await saveOrder('questions', container, questionnaireId, csrfToken);
            }
        });
    });
}

async function saveOrder(type, container, questionnaireId, csrfToken) {
    const dataAttr = type === 'steps' ? 'stepId' : (type === 'blocks' ? 'blockId' : 'questionId');
    const selector = type === 'steps' ? '[data-step-id]' : (type === 'blocks' ? '[data-block-id]' : '[data-question-id]');

    const items = Array.from(container.querySelectorAll(selector)).map((el, idx) => ({
        id: parseInt(el.dataset[dataAttr]),
        sort_order: idx + 1
    }));

    if (items.length === 0) return;

    try {
        const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/reorder`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ type, items }),
        });

        if (!response.ok) {
            console.error('Failed to save order');
        }
    } catch (error) {
        console.error('Failed to save order:', error);
    }
}
