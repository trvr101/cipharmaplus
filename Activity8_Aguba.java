import java.util.Scanner;

public class Activity8_Aguba {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        String[][] champions = {
            {"Saber", "Karina", "Fanny", "Hayabusa", "Lancelot"},    // Assassins
            {"Balmond", "Freya", "Chou", "Sun", "Alpha", "Ruby"},    // Fighters
            {"Eudora", "Gord", "Aurora", "Nana", "Chang'e", "Odette", "Alice"},  // Mages
            {"Miya", "Bruno", "Clint", "Layla", "Hanabi"},           // Marksmen
            {"Diggie", "Rafaela", "Estes", "Angela", "Floryn", "Faramis"}  // Supports
        };

        System.out.println("Champion Selection Program:");
        System.out.println("1. Display All Champions");
        System.out.println("2. Display Champions by Role");
        System.out.println("3. Sort Champions by Role");
        System.out.print("Enter your choice: ");
        int choice = scanner.nextInt();

        if (choice == 1) {
            System.out.println("All Champions:");
            for (String[] role : champions) {
                for (String champion : role) {
                    System.out.print(champion + " ");
                }
                System.out.println();
            }

        } else if (choice == 2) {
            System.out.println("Select a Role:");
            System.out.println("0. Assassins\n1. Fighters\n2. Mages\n3. Marksmen\n4. Supports");
            int roleChoice = scanner.nextInt();

            if (roleChoice >= 0 && roleChoice <= 4) {
                System.out.println("Champions in this role:");
                for (int i = 0; i < champions[roleChoice].length; i++) {
                    System.out.println(i + ": " + champions[roleChoice][i]);
                }
                System.out.print("Pick a champion by number: ");
                int championChoice = scanner.nextInt();

                if (championChoice >= 0 && championChoice < champions[roleChoice].length) {
                    System.out.println("You selected: " + champions[roleChoice][championChoice]);
                } else {
                    System.out.println("Invalid champion selection.");
                }
            } else {
                System.out.println("Invalid role selection.");
            }

        } else if (choice == 3) {
            System.out.println("Sort Champions by Role:");
            System.out.println("0. Assassins\n1. Fighters\n2. Mages\n3. Marksmen\n4. Supports");
            int sortChoice = scanner.nextInt();

            if (sortChoice >= 0 && sortChoice <= 4) {
                System.out.println("Sorted Champions in this role:");
                for (int i = 0; i < champions[sortChoice].length; i++) {
                    System.out.println(i + ": " + champions[sortChoice][i]);
                }
                System.out.print("Pick a champion by number: ");
                int championChoice = scanner.nextInt();

                if (championChoice >= 0 && championChoice < champions[sortChoice].length) {
                    System.out.println("You selected: " + champions[sortChoice][championChoice]);
                } else {
                    System.out.println("Invalid champion selection.");
                }
            } else {
                System.out.println("Invalid sort role selection.");
            }

        } else {
            System.out.println("Invalid menu choice.");
        }

        scanner.close();
    }
}
