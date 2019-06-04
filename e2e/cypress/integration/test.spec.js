describe("The Home Page", function() {
    it("successfully loads", function() {
        cy.visit("/"); // change URL to match your dev URL
        cy.contains(".modal", "Tissue へようこそ！");
        cy.focused() // command
            .should("have.class", "modal")
            .should("be.visible")
            .contains("button", "まかせて")
            .click();
        cy.contains("ログイン").click();

        cy.url().should("include", "/login");
        cy.get("input#email").type("eai@mizle.net");
        cy.get("input#password").type("111111");
        cy.get("body > .container form")
            .contains("ログイン")
            .click();

        cy.get("nav").should("contain", "ホーム");
    });
});
