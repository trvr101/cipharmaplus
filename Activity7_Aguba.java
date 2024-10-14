import java.util.Scanner;

public class Activity7_Aguba {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        System.out.print("Enter the size of the array: ");
        int size = scanner.nextInt();

        int[] numbers = new int[size];

        System.out.println("Enter " + size + " numbers:");
        for (int i = 0; i < size; i++) {
            numbers[i] = scanner.nextInt();
        }

        System.out.println("Choose an operation:");
        System.out.println("1: Sum of all numbers");
        System.out.println("2: Average of all numbers");
        System.out.println("3: Largest number");
        System.out.println("4: Smallest number");
        int choice = scanner.nextInt();

        switch (choice) {
            case 1:
                int sum = 0;
                for (int num : numbers) {
                    sum += num;
                }
                System.out.println("Sum of all numbers: " + sum);
                break;
            case 2:
                int total = 0;
                for (int num : numbers) {
                    total += num;
                }
                double average = (double) total / size;
                System.out.println("Average of all numbers: " + average);
                break;
            case 3:
                int largest = numbers[0];
                for (int num : numbers) {
                    if (num > largest) {
                        largest = num;
                    }
                }
                System.out.println("Largest number: " + largest);
                break;
            case 4:
                int smallest = numbers[0];
                for (int num : numbers) {
                    if (num < smallest) {
                        smallest = num;
                    }
                }
                System.out.println("Smallest number: " + smallest);
                break;
            default:
                System.out.println("Invalid choice! Please select 1, 2, 3, or 4.");
        }
        
        scanner.close();
    }
}
