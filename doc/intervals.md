
### intervals interface and class hierarchy

- **Interval<T>** interface ext. Equalable, Comparable, IntersectComparable
  - *IntInterval<int>* 
    - *NonNegativeIntInterval<int>* 
    - *PositiveIntInterval<int>*
  - *DateInterval<Date>* impl. DateOrTimeInterval
    - *WeekDateInterval<Date>*
  - *NightInterval<Date>* impl. DateOrTimeInterval
  - *DateTimeInterval<DateTime>* impl. DateOrTimeInterval
  - **OpenClosedInterval<T>** interface
    - *FloatInterval<float>*


- **IntervalSet<T>** interface ext. Equalable, IteratorAggregate
  - *IntIntervalSet<IntInterval>*
  - *FloatIntervalSet<FloatInterval>*
  - *DateIntervalSet<DateInterval>* impl. DateOrTimeIntervalSet
  - *NightIntervalSet<NightInterval>* impl. DateOrTimeIntervalSet
  - *DateTimeIntervalSet<DateTimeInterval>* impl. DateOrTimeIntervalSet


- **ModuloInterval<T>** interface ext. Equalable, Comparable
  - *TimeInterval<Time>* impl. DateOrTimeInterval


- **ModuloIntervalSet<T>** interface ext. Equalable, IteratorAggregate
  - *TimeIntervalSet<TimeInterval>* impl. DateOrTimeIntervalSet


- **DateOrTimeInterval** marking interface
- **DateOrTimeIntervalSet** marking interface