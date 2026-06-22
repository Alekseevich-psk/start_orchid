import { Fancybox as FancyboxModule } from "@fancyapps/ui";
const Fancybox = ((window as any).Fancybox = FancyboxModule);

const buttonsForOpenPopup = document.querySelectorAll('[data-popup="order-popup"]') as NodeListOf<HTMLElement>;

buttonsForOpenPopup.forEach(button => {
    button.addEventListener("click", () => {
        Fancybox.show([{ src: "#order-popup", type: "inline" }]);
    });
});

window.addEventListener('fancybox:close', (event) => {
    Fancybox.close();
    const form = document.querySelector('#order-popup form') as HTMLFormElement;

    if (form) {
        form.reset();
    }
});