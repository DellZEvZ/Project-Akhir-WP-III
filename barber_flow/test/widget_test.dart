import 'package:flutter_test/flutter_test.dart';

import 'package:barber_flow/main.dart';

void main() {
  testWidgets('Welcome page tampil', (WidgetTester tester) async {
    await tester.pumpWidget(const BarberFlowApp());

    expect(find.text('REGISTRASI'), findsOneWidget);
    expect(find.text('LOGIN'), findsOneWidget);
  });
}
